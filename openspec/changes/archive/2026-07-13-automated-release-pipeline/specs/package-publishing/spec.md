## ADDED Requirements

### Requirement: Publish on tag via Packagist webhook

The system SHALL rely on the GitHub-to-Packagist webhook to publish a new version when a release tag is pushed, requiring no CI secrets or publish step in any workflow.

#### Scenario: Tag triggers publication

- **WHEN** a `vX.Y.Z` tag is pushed to GitHub (from a merged Release PR)
- **THEN** the Packagist webhook fires on the push and Packagist publishes the tagged version

#### Scenario: No publish secrets in CI

- **WHEN** the release pipeline runs
- **THEN** no `PACKAGIST_*` secret is required and no workflow performs an explicit Packagist API call

### Requirement: Tag-derived versioning is preserved

The system SHALL keep `composer.json` free of a hard-coded `version` field so Packagist derives the version from the git tag name.

#### Scenario: composer.json omits version

- **WHEN** a release tag is published
- **THEN** Packagist reads the version from the tag because `composer.json` declares no `version`
