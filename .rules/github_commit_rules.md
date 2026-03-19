# Git Commit & Push Rules

## 1. Commit Message Format

<type>(optional-scope): short summary

Examples:
feat(auth): add player token verification
fix(api): resolve null response on wins endpoint
refactor(game): simplify reward calculation logic
chore(ci): update GitHub Actions workflow

---

## 2. Commit Types

- feat → new feature
- fix → bug fix
- refactor → code improvement without behavior change
- perf → performance improvement
- chore → maintenance tasks
- docs → documentation only
- test → tests added or updated
- build → build system changes
- ci → CI/CD changes

---

## 3. Commit Message Rules

- Use present tense
- Keep summary under 72 characters
- Be specific and meaningful
- One commit = one logical change

---

## 4. Branch Naming Convention

<type>/<short-description>

Examples:
feat/player-api
fix/upload-timeout
refactor/fireball-controller
chore/update-dependencies

---

## 5. Commit Best Practices

- Commit small, focused changes
- Do not mix unrelated changes
- Ensure code builds and passes tests
- Run linters and formatters before commit

Never commit:
- .env
- secrets / API keys
- unnecessary large files

---

## 6. Push Rules

- Never push directly to main or production
- Always use feature branches and pull requests

Flow:
git checkout -b feat/my-feature
git add .
git commit -m "feat: add new feature"
git push origin feat/my-feature

---

## 7. Pull Request Rules

- Title follows commit format
- Include:
  - what was done
  - why it was done
  - breaking changes if any
- Keep PRs small and reviewable

---

## 8. Squash & Merge

- Use squash merge for clean history
- Ensure final commit message is clear

---

## 9. Restrictions

- Do not include any auto generated signatures
- Do not include Co-authored-by lines
- Do not include any reference to Claude or AI tools in commits

---

## 10. Example Good Commit History

feat(player): add registration endpoint
feat(player): implement token authentication
fix(player): handle duplicate email validation
refactor(player): extract service layer logic
