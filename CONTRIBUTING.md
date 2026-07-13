# Contributing

## Commit messages

This project uses [Conventional Commits](https://www.conventionalcommits.org/). Every commit is linted on pull requests, and the release version is derived from these types:

| Prefix | Effect on the next release |
|---|---|
| `fix:` | patch bump (`0.1.0` → `0.1.1`) |
| `feat:` | minor bump (`0.1.0` → `0.2.0`) |
| `feat!:` or a `BREAKING CHANGE:` footer | pre-1.0: minor bump (0.x stays 0.x) |
| `chore:`, `docs:`, `test:`, `ci:`, `refactor:`, … | no release |

Because this repository uses **merge commits** (not squash), every commit in a pull request must follow the convention — the `commitlint` check lints each one (GitHub's own merge commits are ignored).

## How releases work

Releases are automated with [release-please](https://github.com/googleapis/release-please); you never tag or edit the changelog by hand.

```
  Conventional commits merged to main
        └─▶ release-please opens/updates a "Release PR"
              (computed version + CHANGELOG.md)
        └─▶ a maintainer merges the Release PR
              ├─ creates the vX.Y.Z tag + GitHub Release
              └─▶ the new version is published to Packagist
```

The project starts at `0.1.0` with 0.x semantics (breaking changes bump the minor, not to 1.0.0) until the API stabilises.
