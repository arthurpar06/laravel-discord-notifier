# quality-gates Specification

## Purpose

Hold the package to the strictest achievable code-quality bar by enforcing static analysis at PHPStan's maximum level, full type coverage, minimum line coverage, and a minimum mutation score — both locally through `composer` scripts and in a dedicated CI job — so regressions in analysis, typing, or test strength block a merge.

## Requirements

### Requirement: PHPStan runs at maximum level with no suppressions

The system SHALL configure PHPStan (larastan) at level `10` (max) over the `src` directory, and SHALL NOT suppress any error via a baseline entry, `@phpstan-ignore` comment, inline `@var` override, or type cast added solely to silence analysis.

#### Scenario: Static analysis passes at max level

- **WHEN** `composer analyse` is run
- **THEN** PHPStan analyses `src` at level 10 and reports no errors

#### Scenario: Baseline stays empty

- **WHEN** the repository is inspected
- **THEN** `phpstan-baseline.neon` contains no suppressed errors

### Requirement: Type coverage is fully enforced

The system SHALL declare types on every parameter, return, and property across `src`, and SHALL enforce a 100% type-coverage minimum so that a regression fails the check.

#### Scenario: Type coverage meets the minimum

- **WHEN** `composer test-type-coverage` is run
- **THEN** Pest reports 100% type coverage and exits successfully

#### Scenario: Missing type declaration fails the gate

- **WHEN** a parameter, return, or property in `src` is left untyped
- **THEN** the type-coverage check reports below 100% and exits non-zero

### Requirement: Line coverage minimum is enforced

The system SHALL run the test suite with a code-coverage driver and SHALL fail when line coverage over `src` falls below a defined minimum threshold. The threshold SHALL be set from a measured baseline at or above the suite's current coverage, never below it.

#### Scenario: Coverage at or above threshold passes

- **WHEN** `composer test-coverage` is run and line coverage is greater than or equal to the configured minimum
- **THEN** the command exits successfully

#### Scenario: Coverage regression fails

- **WHEN** a change drops line coverage below the configured minimum
- **THEN** the coverage command exits non-zero

### Requirement: Mutation score minimum is enforced

The system SHALL run mutation testing (via `pest-plugin-mutate`) and SHALL fail when the mutation score falls below a defined minimum threshold. The threshold SHALL be set from a measured baseline and reflect that surviving mutants indicate tests that do not assert behavior.

#### Scenario: Mutation score at or above threshold passes

- **WHEN** `composer test-mutate` is run and the mutation score is greater than or equal to the configured minimum
- **THEN** the command exits successfully

#### Scenario: Weak tests fail the gate

- **WHEN** mutation testing injects a change that no test catches, dropping the score below the configured minimum
- **THEN** the mutation command exits non-zero

### Requirement: A dedicated CI job enforces all quality gates

The system SHALL enforce every quality gate (PHPStan level, type coverage, line coverage, mutation score) in continuous integration through a dedicated quality job that runs on a single PHP and Laravel version with a coverage driver available. The existing compatibility matrix SHALL remain on `coverage: none` and SHALL NOT run the slow coverage or mutation gates.

#### Scenario: Quality job blocks a regressing pull request

- **WHEN** a pull request is opened that regresses any enforced gate
- **THEN** the dedicated CI quality job fails and the pull request is not mergeable on that check

#### Scenario: Compatibility matrix stays fast

- **WHEN** the compatibility matrix jobs run
- **THEN** they execute the test suite without a coverage driver and do not run the mutation or coverage gates

### Requirement: Quality gates are runnable locally

The system SHALL expose each quality gate as a `composer` script so a contributor can run static analysis, type coverage, line coverage, and mutation testing locally with the same thresholds enforced in CI.

#### Scenario: Contributor runs the gates before pushing

- **WHEN** a contributor runs the documented `composer` scripts (`analyse`, `test-type-coverage`, `test-coverage`, `test-mutate`)
- **THEN** each script applies the same threshold enforced by CI
