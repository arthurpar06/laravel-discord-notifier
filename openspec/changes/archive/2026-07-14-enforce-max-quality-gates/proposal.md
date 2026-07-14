## Why

The package ships real, type-heavy code (embeds, transports, routing, serialization) but its quality bar is set well below what the codebase can already sustain: PHPStan runs at level 5, there is no type-coverage gate, and CI runs `coverage: none` with no line- or mutation-coverage floor. The headroom is unusually cheap to claim — levels 5 and 6 already pass, and the entire jump to level 10 (max) is only 4 errors in 2 files — while `pestphp/pest-plugin-mutate` is already a dev dependency sitting unused. Locking in a maximal, enforced quality bar now, while the codebase is small, is far cheaper than retrofitting it later.

## What Changes

- Raise PHPStan from level 5 to **level 10 (max)** in `phpstan.neon.dist`, fixing the 4 real type-safety gaps (container `make()` returning `mixed` in `DiscordNotifierServiceProvider`; `method_exists()` on `class-string|object` in `DiscordChannel`) rather than baselining them. The empty `phpstan-baseline.neon` stays empty — no suppressions.
- Add `pestphp/pest-plugin-type-coverage` and enforce **100% type coverage** (`--type-coverage --min=100`), filling any missing param/return/property type declarations.
- Enable **line-coverage** enforcement: add a coverage driver (pcov) to CI and gate on `pest --coverage --min=<threshold>`.
- Enable **mutation-testing** enforcement using the already-installed `pest-plugin-mutate`, gating on `pest --mutate --min=<threshold>`.
- Expand the test suite (currently 25 tests / 35 assertions) as needed to meet the aspirational line and mutation thresholds.
- Add a **dedicated CI "quality" job** (single PHP 8.5 + Laravel 13, with pcov) that runs PHPStan, type coverage, line coverage, and mutation testing — keeping the existing 24-job compatibility matrix fast on `coverage: none`.
- Add `composer` scripts (`test-type-coverage`, `test-mutate`) and update the stale `test-coverage` flow so all gates are runnable locally.

Threshold values for line and mutation coverage are **measured from a real baseline run first, then set at or above it** — not guessed. Type coverage targets a fixed 100%.

## Capabilities

### New Capabilities
- `quality-gates`: The static-analysis and coverage bar the codebase must clear — PHPStan level, type-coverage minimum, line-coverage minimum, mutation-score minimum — and the CI enforcement point (a dedicated quality job) that blocks merges when any gate regresses.

### Modified Capabilities
<!-- None. No existing capability's requirements change; this introduces a new enforcement capability. -->

## Impact

- **Source**: `src/DiscordNotifierServiceProvider.php`, `src/Notifications/DiscordChannel.php` (type fixes); possibly other files for 100% type coverage.
- **Config**: `phpstan.neon.dist` (level 10), `composer.json` (new dev dep + scripts).
- **Dependencies**: adds `pestphp/pest-plugin-type-coverage` (require-dev); activates existing `pestphp/pest-plugin-mutate`.
- **CI**: new `quality` job in `.github/workflows/run-tests.yml` (or a dedicated workflow) requiring a pcov coverage driver.
- **Tests**: `tests/` expanded to satisfy line and mutation thresholds.
- **Non-goals / not in scope**: fixing the stale `CLAUDE.md` and the placeholder `phpunit.xml.dist` testsuite name (`VendorName Test Suite`) — noted but tracked separately.
