# Git Rules

## Commit Authorship

- **Never add Claude as a co-author.** Do not append `Co-Authored-By: Claude` or any Anthropic AI trailer to commit messages.
- Commits should reflect only the human author(s) as recorded by git config.

## Commit Style

- Use conventional commit prefixes: `fix`, `feat`, `chore`, `docs`, `refactor`, `test`, `style`.
- Keep the subject line under 72 characters.
- Use the imperative mood ("add typography support", not "added" or "adds").
- Group related changes into a single commit; split unrelated changes into separate commits.
- Tooling/config changes (linting setup, rule files, config files) belong in a separate commit from functional code changes.

## Workflow

- Never force-push to `main` or `master`.
- Never skip hooks (`--no-verify`) unless explicitly instructed.
- Never amend a published commit.
- Stage specific files by name — avoid `git add .` or `git add -A`.
