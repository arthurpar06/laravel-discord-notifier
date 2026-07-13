## 1. Remove the conflicting manual-release setup

- [x] 1.1 Delete `.github/workflows/update-changelog.yml`
- [x] 1.2 Reset `CHANGELOG.md` to a clean header (drop skeleton placeholder text; keep an "All notable changes ..." intro release-please can append under)

## 2. release-please configuration

- [x] 2.1 Add `release-please-config.json` (release type resolved at design — `simple` or `php`, tag format `vX.Y.Z`, changelog on, package at repo root)
- [x] 2.2 Add `.release-please-manifest.json` seeded to `0.1.0`
- [x] 2.3 Confirm 0.x bump semantics are in effect (breaking → minor while under 1.0)

## 3. release-please workflow

- [x] 3.1 Add `.github/workflows/release-please.yml` triggered on push to `main`, with `contents: write` and `pull-requests: write` permissions
- [x] 3.2 Pin the release-please action to a major version and wire it to the config/manifest above
- [x] 3.3 Verify the workflow produces a Release PR (version + CHANGELOG) and, on merge, a `vX.Y.Z` tag + GitHub Release

## 4. Commit message enforcement

- [x] 4.1 Add `commitlint.config.js` extending `@commitlint/config-conventional`
- [x] 4.2 Add `.github/workflows/commitlint.yml` on `pull_request` that lints every commit in the PR (merge-commit workflow) and ignores GitHub merge commits
- [x] 4.3 Verify a non-conventional commit fails the check and a conventional one passes

## 5. Documentation (manual/external steps)

- [x] 5.1 Add a "Releasing" section documenting the one-time Packagist registration of `arthurpar06/laravel-discord-notifier` and connecting the GitHub webhook
- [x] 5.2 Document the recommended branch-protection rule on `main` (require PRs + passing commit-lint check)
- [x] 5.3 Document the day-to-day release flow (Conventional Commits → merge Release PR → auto tag/changelog/Packagist)

## 6. Verify the pipeline end to end

- [x] 6.1 Confirm `composer.json` still omits a `version` field (tag-derived versioning)
- [x] 6.2 Dry-run/validate the workflows (YAML valid; permissions correct) before relying on them
- [ ] 6.3 After merge to main and the first Release PR, confirm the initial release targets `0.1.0` and that the tag publishes on Packagist once the webhook is connected (deferred: live check, requires this branch merged + webhook connected)
