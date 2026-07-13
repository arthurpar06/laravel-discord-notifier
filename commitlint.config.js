// Conventional Commits enforcement for pull requests.
// Runs inside the commitlint GitHub Action — no npm dependency is added to the
// package itself. The versioning engine (release-please) relies on these types.
module.exports = {
    extends: ['@commitlint/config-conventional'],
};
