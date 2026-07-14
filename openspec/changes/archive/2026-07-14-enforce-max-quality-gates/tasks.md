## 1. PHPStan to level 10 (max)

- [x] 1.1 Fix the `mixed` type from container resolution in `src/DiscordNotifierServiceProvider.php:30` (type the resolved binding / closure return; no cast, no ignore)
- [x] 1.2 Fix `method_exists()` on `class-string|object` in `src/Notifications/DiscordChannel.php:23-24` (narrow with `instanceof` / proper typing before the method call)
- [x] 1.3 Resolve any remaining level 7–10 errors surfaced by `vendor/bin/phpstan analyse --level=10` (fixes only — no `@phpstan-ignore`, inline `@var`, silencing casts, or baseline entries)
- [x] 1.4 Set `level: 10` in `phpstan.neon.dist` and confirm `phpstan-baseline.neon` is still empty
- [x] 1.5 Verify `composer analyse` reports no errors

## 2. Type coverage 100%

- [x] 2.1 Add `pestphp/pest-plugin-type-coverage` to `require-dev` (`composer require --dev`)
- [x] 2.2 Run `vendor/bin/pest --type-coverage` and add missing param/return/property type declarations across `src` until it reports 100% (already 100%, no gaps)
- [x] 2.3 Add a `test-type-coverage` composer script running `pest --type-coverage --min=100`
- [x] 2.4 Verify `composer test-type-coverage` passes at 100%

## 3. Line-coverage baseline and gate

- [x] 3.1 In a driver-equipped environment (pcov/xdebug), run `vendor/bin/pest --coverage` and record the current line-coverage percentage over `src` (baseline 83.4%)
- [x] 3.2 Fix the `test-coverage` composer script to enforce a `--min` pinned at the measured baseline (no regression), replacing the current unenforced form (tests raised coverage to 100%; pinned `--min=100`)
- [x] 3.3 Verify `composer test-coverage` passes at the pinned `--min`

## 4. Mutation-testing baseline and gate

- [x] 4.1 Run `vendor/bin/pest --mutate` (using the already-installed `pest-plugin-mutate`) and record the current mutation score; note which files/mutants survive (baseline 66.53%; requires `--everything`; enum/const declaration mutants are line-coverage-invisible so scored via `--covered-only`)
- [x] 4.2 Add a `test-mutate` composer script running `pest --mutate --min=<baseline>` pinned at the measured baseline (`--mutate --everything --covered-only --min=97`)
- [x] 4.3 Verify `composer test-mutate` passes at the pinned `--min`

## 5. Expand tests toward aspirational thresholds

- [x] 5.1 Add/strengthen tests targeting the surviving mutants from 4.1 (assert behavior, not just execution) — `tests/MutationTest.php`; 5 documented equivalent mutants remain
- [x] 5.2 Add tests for any uncovered lines from 3.1 (transports, routing, serialization, exceptions, enums as needed) — `tests/EmbedTest.php` + channel/route additions
- [x] 5.3 Ratchet the line-coverage `--min` upward toward the aspirational target as coverage improves (100%)
- [x] 5.4 Ratchet the mutation `--min` upward toward the aspirational target as the score improves (97% floor, 97.49% actual)
- [x] 5.5 Confirm the full local suite (`composer test`, `test-type-coverage`, `test-coverage`, `test-mutate`, `analyse`) is green at the final thresholds

## 6. Dedicated CI quality job

- [x] 6.1 Add a `quality` job (single PHP 8.5 + Laravel 13) with a pcov coverage driver, separate from the compatibility matrix (`.github/workflows/quality.yml`; redundant `phpstan.yml` removed)
- [x] 6.2 Have the quality job run PHPStan, type coverage, line coverage, and mutation testing (via the composer scripts) with `--min` enforcement
- [x] 6.3 Confirm the existing compatibility matrix still runs `coverage: none` and does not run the coverage/mutation gates (`run-tests.yml` unchanged: `coverage: none`, `pest --ci`)
- [x] 6.4 Confirm the quality job fails on an intentional gate regression (sanity check) and passes on the green branch (verified gate blocks via impossible `--min`; all gates green on branch)

## 7. Documentation

- [x] 7.1 Document the quality gates and their local `composer` scripts in `CONTRIBUTING.md` (and README if appropriate)
- [x] 7.2 Record the final pinned thresholds (PHPStan level, type %, line %, mutation %) where contributors will see them (CONTRIBUTING.md table)
