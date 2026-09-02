# Fossil

## Workflow

- Commit and push directly to `main`. This repo doesn't use feature branches or PRs — a branch left unmerged just means the change never deploys. Deploys run from `main` via CI to the air host.

## Hidden characters

- Characters listed in `hidden_characters.json` (a JSON array of names in `STATE_DIR`, loaded by `hiddenCharacters()` in `config.php`) are hidden from all UI pages and data endpoints via `isHiddenCharacter()` / `hiddenCharactersCondition()`. They are still scraped and stored normally. Any new read path (page or JSON endpoint) must apply the same filter.
- Character names are deployment data, never source. Keep them in the `STATE_DIR` JSON lists (`hidden_characters.json`, `watched_players.json`) — do not hardcode a real character name in code, comments, migrations, fixtures or commit messages.
