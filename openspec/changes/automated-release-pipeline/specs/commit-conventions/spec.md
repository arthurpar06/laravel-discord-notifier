## ADDED Requirements

### Requirement: Commit message enforcement on pull requests

The system SHALL validate that commit messages follow the Conventional Commits specification on every pull request, failing the check when a commit does not conform, so the versioning engine always has well-typed input.

#### Scenario: Conforming commits pass

- **WHEN** every non-merge commit in a pull request follows Conventional Commits (e.g. `feat:`, `fix:`, `chore:`)
- **THEN** the commit-lint check passes

#### Scenario: Non-conforming commit fails

- **WHEN** a pull request contains a commit whose message does not follow Conventional Commits (e.g. `wip stuff`)
- **THEN** the commit-lint check fails and blocks the merge

### Requirement: Lint every commit under a merge-commit workflow

The system SHALL lint all individual commits in a pull request (not only a PR title), because the project merges commits rather than squashing, and SHALL ignore GitHub-generated merge commits.

#### Scenario: All PR commits checked

- **WHEN** a pull request contains multiple commits
- **THEN** each non-merge commit is validated independently

#### Scenario: Merge commits ignored

- **WHEN** a pull request branch contains a GitHub-generated merge commit (e.g. `Merge pull request #12 ...`)
- **THEN** that merge commit is not flagged by the commit-lint check
