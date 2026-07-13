## Context

The package ships the Spatie skeleton's CI: `run-tests.yml`, `phpstan.yml`, `fix-php-code-style-issues.yml`, and `update-changelog.yml`. The last one is built around a **manual** GitHub Release — a human tags and writes notes, and the workflow only folds those notes into `CHANGELOG.md`. That model is incompatible with the goal of automatic versioning.

`composer.json` correctly omits the `version` field, so Packagist derives versions from git tags. There is no Packagist registration or webhook yet. Recent history already uses Conventional Commits (`feat:`, `fix:`, `chore:`, `docs:`, `test:`), and the team merges commits rather than squashing. All decisions below were settled during exploration.

## Goals / Non-Goals

**Goals:**
- Cutting a release is: merge Conventional-Commit work → merge a generated Release PR → version, changelog, GitHub Release, and Packagist publish happen automatically.
- No CI secrets for publishing.
- Enforce Conventional Commits so the versioning input is always well-typed.
- Keep the change to tooling/config only — no PHP package code touched.

**Non-Goals:**
- Automating Packagist registration or webhook creation (one-time manual, documented).
- Fully zero-touch releases (a maintainer still merges the Release PR — this gate is deliberate).
- Automating branch-protection setup (a GitHub repo setting, documented not committed).
- Reaching 1.0.0 or committing to stable semver yet (starting 0.x).
- Adding a Node/Composer dependency to the package for tooling.

## Decisions

### release-please (gated-auto) as the engine
release-please watches `main`, maintains a Release PR with the computed version + changelog, and creates the tag + GitHub Release on merge.
*Alternatives:* **semantic-release** (fully zero-touch, tags on every releasing push) — rejected for removing the human ship-it gate and being Node-ecosystem heavy; **manual + changelog-updater** (the skeleton's model) — rejected because it is not automatic versioning. release-please keeps automation while preserving a merge-time gate, and the tag it creates is what triggers Packagist.

### Start at 0.1.0 with 0.x semantics
Seed `.release-please-manifest.json` at `0.1.0`. While under 1.0, breaking changes bump the minor, signaling an unstable API (components/V2 still to come).
*Alternative:* plant 1.0.0 now — rejected as premature stability commitment for a young API.

### Packagist via webhook only
The `vX.Y.Z` tag push fires the GitHub→Packagist webhook, which publishes. No workflow step, no secrets.
*Alternative:* add a post-release Packagist API `POST` (needs `PACKAGIST_USERNAME`/`PACKAGIST_TOKEN`) — rejected for now as a fix for an unobserved problem; it is a small backward-compatible follow-up if the webhook ever proves flaky. Note: this webhook is a GitHub-native repo hook to an external service, so the "GITHUB_TOKEN doesn't trigger other workflows" limitation does not apply — release-please-created tags still fire it.

### commitlint on pull_request, linting every commit
Use the commitlint GitHub Action with `@commitlint/config-conventional`. Because the project uses merge commits, it lints each commit in the PR (not a PR title), and it ignores GitHub merge commits by default. commitlint runs inside the Action, so only a small `commitlint.config.js` lives in the repo — no npm dependency added to the package.
*Alternative:* PR-title lint only — rejected because with merge commits the individual commit messages are what land on `main` and drive versioning.

### Branch protection documented, not committed
Enforcement on PRs can be bypassed by direct pushes to `main`, so the companion control is a branch-protection rule (require PRs + the commitlint check). That is a GitHub setting, so it is documented in a "Releasing"/contribution section rather than expressed as a file.

### Remove update-changelog.yml, reset CHANGELOG.md
release-please owns `CHANGELOG.md`; keeping the skeleton workflow would double-write and conflict. Delete it and reset `CHANGELOG.md` to a clean header so release-please appends from a known baseline.

## Risks / Trade-offs

- **A commit merged to `main` without a conventional type is silently non-releasing** → mitigated by the commitlint PR gate + documented branch protection requiring PRs.
- **Direct pushes to `main` bypass commitlint** → mitigated by the documented branch-protection rule; residual risk accepted until that rule is enabled.
- **Webhook could silently fail, delaying a publish** → accepted for now; Packagist still crawls periodically, and the API-push step is an easy later addition.
- **release-please tooling/action versions drift** → pin the action to a major version; revisit at implementation time.
- **First run needs the manifest seeded correctly** or release-please may mis-detect the starting version → seed `.release-please-manifest.json` to `0.1.0` explicitly and verify the first Release PR targets `0.1.0`.

## Open Questions

- Tag format: `vX.Y.Z` vs `X.Y.Z`. Composer/Packagist accept both; default to `vX.Y.Z` unless preferred otherwise — confirm at implementation.
- release-please release type: `php` vs `simple`. `simple` (version files only) is likely sufficient since there is no `version` in `composer.json`; confirm which cleanly produces the tag + changelog for a Composer package.
