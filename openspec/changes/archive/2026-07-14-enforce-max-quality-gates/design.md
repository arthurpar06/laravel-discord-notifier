## Context

The package is a Spatie-style Laravel library with a small, strongly-typed surface (embeds, transports, routing, serialization). Current quality tooling: PHPStan level 5 (`phpstan.neon.dist`), an empty `phpstan-baseline.neon`, Pest 4 on Orchestra Testbench (25 tests / 35 assertions), and a 24-job compatibility matrix (`.github/workflows/run-tests.yml`) running `coverage: none`. `pestphp/pest-plugin-mutate` is already in `require-dev` but unused.

Measured headroom (verified locally):
- PHPStan level 5 and 6 both pass with zero errors.
- Levels 7–8 report 1 error, level 9 reports 2, level 10 reports 4 — all concentrated in two files: `DiscordNotifierServiceProvider.php:30` (container `make()` returns `mixed`) and `DiscordChannel.php:23-24` (`method_exists()` on `class-string|object`, then a method call on it).
- No coverage driver is installed locally, so line and mutation baselines cannot be measured in this environment — they must be measured in CI (or a driver-equipped environment) before thresholds are fixed.

This change is enforcement-and-tooling work, not feature work.

## Goals / Non-Goals

**Goals:**
- PHPStan level 10 (max) over `src`, green, with zero suppressions.
- 100% enforced type coverage.
- Enforced line-coverage and mutation-score minimums, set from measured baselines (aspirational: raise the suite to meet high thresholds, not lower thresholds to meet the suite).
- A single dedicated CI quality job as the enforcement point; compatibility matrix stays fast.
- All gates runnable locally via `composer` scripts.

**Non-Goals:**
- Refactoring runtime behavior of the notifier. Type fixes must be behavior-preserving.
- Fixing the stale `CLAUDE.md` (describes an unmodified skeleton) or the placeholder `phpunit.xml.dist` testsuite name — noted, tracked separately.
- Adding coverage/mutation gates to every matrix cell.

## Decisions

### D1: Jump straight to PHPStan level 10, fix the 4 errors, keep the baseline empty
The gap is tiny (4 errors, 2 files) and every error is a genuine type-safety hole, so fixing is cheaper and more valuable than an intermediate stop at level 8. Fixes use real narrowing (`instanceof`, typed closures/resolved bindings), **not** `@phpstan-ignore`, inline `@var`, or silencing casts.
- *Alternatives:* Stop at level 6 (free but leaves real holes) or level 8 (still needs a fix, still leaves level 9–10 mixed-handling holes). Rejected given how cheap max is here.

### D2: Type coverage via `pest-plugin-type-coverage`, gate at `--min=100`
Type coverage (presence of declarations) is orthogonal to PHPStan (correctness of declarations); both are wanted. 100% is achievable on a young codebase and makes the gate unambiguous — any missing declaration fails.
- *Alternatives:* A sub-100 threshold — rejected as it invites drift with no clear line.

### D3: Activate the already-installed mutation plugin; treat mutation score as the primary behavioral gate
`pest-plugin-mutate` is paid-for and gives a far stronger signal than line coverage (it proves tests *assert*, not just *execute*). Line coverage is kept as a cheaper complementary floor.
- *Alternatives:* Line coverage only — rejected as it can pass with shallow assertions.

### D4: Thresholds are measured-first, then pinned
Line and mutation `--min` values are not invented. Implementation measures the baseline in a driver-equipped run, records the number, then pins `--min` at or above it. Under the "aspirational" strategy, the suite is then expanded and the thresholds raised toward high targets (line ≥ ~90%, mutation ≥ ~80% as directional aims, confirmed against the real baseline).
- *Alternatives:* Guess thresholds now — rejected; a fabricated number either blocks nothing or blocks arbitrarily.

### D5: pcov as the CI coverage driver
pcov is fast and sufficient for line coverage and mutation testing; Xdebug is slower. Installed only in the dedicated quality job.
- *Alternatives:* Xdebug — rejected for speed.

### D6: One dedicated quality job, not matrix-wide coverage
A single job (PHP 8.5 + Laravel 13, pcov) runs PHPStan + type coverage + line coverage + mutation. The existing 24-cell matrix stays `coverage: none`. This avoids running slow gates 24× (and blowing the 5-minute job timeout) while still gating every PR.
- *Alternatives:* Fold coverage into the matrix — rejected (wasteful, timeout risk). Separate workflow file vs. an added job — either works; prefer an added `quality` job unless separation reads cleaner.

## Risks / Trade-offs

- **Level 10 raises the ongoing maintenance bar** (every `config()` / container `mixed` must be handled explicitly as the package grows) → accepted deliberately; small codebase makes discipline cheap now.
- **Aspirational thresholds mean real test-writing effort**, concentrated in getting mutation score high → scope this as the bulk of the work; land tooling first, then iterate tests until the gate is green.
- **Thresholds can't be measured in the current environment** → measurement is an explicit CI (or driver-equipped) task before pinning `--min`; do not hardcode a number blindly.
- **Mutation testing may be slow / flaky on the full suite** → confine to the quality job; if runtime is excessive, scope mutation to `src` and tune `--parallel`, but do not lower the min to hide weak tests.
- **A too-high initial `--min` could block the first PR** → pin at the measured baseline first (no-regression), then raise in follow-up commits within this change as tests are added.

## Migration Plan

1. Land type fixes + `phpstan.neon.dist` level 10 (green, empty baseline).
2. Add `pest-plugin-type-coverage`, reach 100%, add the `test-type-coverage` script.
3. In a driver-equipped run, measure line + mutation baselines; pin `--min` at baseline; add `test-mutate` and fix the `test-coverage` script.
4. Add the dedicated CI quality job (pcov) running all four gates.
5. Expand tests and ratchet line/mutation `--min` upward toward the aspirational targets until green.

Rollback: revert the `phpstan.neon.dist` level bump and remove the quality job / `--min` flags; the type fixes and new tests are safe to keep.

## Open Questions

- Exact pinned values for line and mutation `--min` — resolved by the baseline measurement in step 3, not before.
- Dedicated `quality.yml` workflow vs. an added job in `run-tests.yml` — decide at implementation based on readability.
