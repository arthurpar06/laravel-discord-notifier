# release-automation Specification

## Purpose

Turn Conventional Commits on `main` into releases automatically: compute the next version, generate the changelog, and — gated by a maintainer merging a Release PR — create the git tag and GitHub Release, without anyone choosing version numbers or writing changelog entries by hand.

## Requirements

### Requirement: Conventional-Commit-driven versioning

The system SHALL compute the next release version from Conventional Commit messages merged to `main` using release-please, without a human choosing the number: `fix:` yields a patch bump, `feat:` a minor bump, and a breaking change (`!` or a `BREAKING CHANGE:` footer) a major-level bump.

#### Scenario: Feature commit bumps the minor

- **WHEN** commits containing a `feat:` entry are merged to `main` since the last release
- **THEN** the pending release version increments the minor component

#### Scenario: Fix commit bumps the patch

- **WHEN** only `fix:` (and non-releasing) commits are merged since the last release
- **THEN** the pending release version increments the patch component

### Requirement: 0.x version seed and semantics

The system SHALL start versioning at `0.1.0` and apply 0.x semantics while below 1.0.0, so that a breaking change increments the minor component rather than promoting to 1.0.0.

#### Scenario: Initial version

- **WHEN** the release automation runs before any release exists
- **THEN** it targets `0.1.0` as the first version

#### Scenario: Breaking change stays in 0.x

- **WHEN** a breaking change is released while the current version is `0.y.z`
- **THEN** the next version increments the minor (e.g. `0.2.0`), not `1.0.0`

### Requirement: Generated changelog

The system SHALL generate and maintain `CHANGELOG.md` from the released commits as part of the release process, and this SHALL be the single source of changelog updates (no separate changelog workflow).

#### Scenario: Changelog updated on release

- **WHEN** a release is prepared
- **THEN** `CHANGELOG.md` gains a section for the new version listing its changes grouped by type

#### Scenario: No competing changelog automation

- **WHEN** the release automation is in place
- **THEN** the skeleton's `update-changelog.yml` workflow is absent so the changelog is not written twice

### Requirement: Gated tag and GitHub Release

The system SHALL propose each release as a Release PR that a maintainer merges, and only on that merge SHALL it create the `vX.Y.Z` git tag and a corresponding GitHub Release.

#### Scenario: Release PR maintained

- **WHEN** releasable commits exist on `main`
- **THEN** a Release PR is opened or updated showing the computed version and changelog

#### Scenario: Merge cuts the release

- **WHEN** the maintainer merges the Release PR
- **THEN** a `vX.Y.Z` tag and a GitHub Release are created for that version

#### Scenario: No releasable commits

- **WHEN** only non-releasing commits (e.g. `chore:`, `docs:`) are on `main` since the last release
- **THEN** no Release PR proposes a version bump
