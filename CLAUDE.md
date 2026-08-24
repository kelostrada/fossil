# Fossil

## Workflow

- Commit and push directly to `main`. This repo doesn't use feature branches or PRs — a branch left unmerged just means the change never deploys. Deploys run from `main` via CI to the air host.

## Hidden characters

- Characters listed in `hiddenCharacters` in `config.php` (configured in STATE_DIR) are hidden from all UI pages and data endpoints via `isHiddenCharacter()` / `hiddenCharactersCondition()`. They are still scraped and stored normally. Any new read path (page or JSON endpoint) must apply the same filter.
