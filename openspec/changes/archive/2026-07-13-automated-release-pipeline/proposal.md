## Why

Releasing the package today is entirely manual and the skeleton's `update-changelog.yml` even assumes a hand-created GitHub Release, which contradicts any notion of automatic versioning. We want cutting a release to be: merge Conventional-Commit work to `main`, merge a generated Release PR, and have the version, changelog, GitHub Release, and Packagist publication all happen on their own.

## What Changes

- Add **release-please** (gated-auto) as the release engine: it reads Conventional Commits on `main`, maintains a Release PR that computes the next semver and updates `CHANGELOG.md`, and on merge creates a `vX.Y.Z` tag + GitHub Release.
- Seed versioning at **0.1.0** with **0.x semantics** (a breaking change bumps the minor, not to 1.0).
- **Packagist publishes via webhook only** — the tag push triggers publication with no CI secrets. Registering the package on Packagist and connecting the webhook is a one-time manual step, documented (not automated).
- Add **commitlint** enforcement on `pull_request` using the conventional config: it lints every commit in the PR (merge-commit workflow, not squash) and ignores GitHub-generated merge commits. **BREAKING for contributor workflow**: non-conventional commit messages will fail CI.
- Document a recommended **branch-protection rule on `main`** (require PRs + the commitlint check) so the enforcement can't be bypassed by direct pushes. This is a GitHub repo setting, documented rather than committed.
- **Remove** `.github/workflows/update-changelog.yml` (it double-writes/conflicts with release-please's changelog) and **reset** `CHANGELOG.md` to a clean starting state.

## Capabilities

### New Capabilities
- `release-automation`: Conventional-Commits-driven version computation, changelog generation, and GitHub Release/tag creation via release-please, including the `0.1.0` start and 0.x bump semantics.
- `package-publishing`: Publication of tagged releases to Packagist via the GitHub webhook, and the documented one-time registration/webhook setup.
- `commit-conventions`: Enforcement of Conventional Commit messages on pull requests as the input contract the versioning engine depends on.

### Modified Capabilities
<!-- None — no existing specs cover release tooling. -->

## Impact

- **Workflows**: adds `.github/workflows/release-please.yml` and `.github/workflows/commitlint.yml`; deletes `.github/workflows/update-changelog.yml`. Existing `run-tests.yml`, `phpstan.yml`, `fix-php-code-style-issues.yml` are untouched.
- **New config files**: `release-please-config.json`, `.release-please-manifest.json` (seeded `0.0.0`), and `commitlint.config.cjs` (extends `@commitlint/config-conventional`). No PHP/Composer dependencies added — commitlint runs inside its Action.
- **Repo files**: `CHANGELOG.md` reset; README/CONTRIBUTING gains a "Releasing" section documenting the Packagist webhook and branch-protection setup.
- **`composer.json`**: unchanged — it already omits the `version` field, which Packagist requires for tag-derived versioning.
- **Contributor workflow**: commit messages must follow Conventional Commits; `main` should require PRs.
- **External, manual (out of CI)**: register `arthurpar06/laravel-discord-notifier` on Packagist, connect the GitHub webhook, and add the branch-protection rule.
