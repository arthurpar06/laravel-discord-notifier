# Contributing

## Quality gates

Every pull request must clear four enforced gates. They run in the dedicated
`quality` GitHub Actions job (a single PHP 8.5 + Laravel 13 run with the `pcov`
coverage driver); the compatibility matrix in `run-tests.yml` only runs the
suite. Run them locally with:

| Command | Gate | Threshold |
|---|---|---|
| `composer analyse` | PHPStan (larastan) static analysis | level `max` (10), no baseline/suppressions |
| `composer test-type-coverage` | Type declaration coverage | `100%` |
| `composer test-coverage` | Line coverage over `src` | `100%` |
| `composer test-mutate` | Mutation score (`--everything --covered-only`) | `≥ 97%` |

The last two need a coverage driver (`pcov` or Xdebug) installed in your PHP
CLI; without one Pest reports "No code coverage driver is available."

The mutation floor sits at 97% (actual score 97.49%): five surviving mutants are
equivalent — `array_values()` on a variadic-built list, a `(string)` cast on an
already-string value, and a redundant empty-string route guard whose inner check
rejects the same input — so they cannot be killed without contorting the source.
Raise the `--min` values as coverage improves; never lower them to hide weak tests.

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
