# UI test screenshots

A tiny, DB-free harness for eyeballing the layout shell and the 8 design
themes across light/dark and desktop/mobile. It renders
`templates/layout.php` with representative mock content and screenshots it
with headless Chromium, so layout/alignment/overflow regressions are easy
to catch (and for an assistant to interpret).

## Requirements

- Docker (pulls `php:8.2-cli` and `zenika/alpine-chrome` on first run)

## Usage

```bash
tools/uitest/shoot.sh                                   # full matrix → tools/uitest/shots/
tools/uitest/shoot.sh --design 3 --view mobile --mode dark --page online
tools/uitest/shoot.sh --design 3 --view mobile --menu   # mobile sidebar drawer open
```

Options (comma-separated lists):

| flag | values | default |
|------|--------|---------|
| `--design` | `1`..`8` | `1..8` |
| `--mode`   | `light`, `dark` | both |
| `--view`   | `desktop` (1280), `mobile` (390) | both |
| `--page`   | `dashboard`, `online`, `killers`, `wide` | `dashboard` |
| `--menu`   | (flag) force the mobile drawer open | off |

PNGs are written to `tools/uitest/shots/` and intermediate HTML to
`tools/uitest/out/` — both are git-ignored.

## Notes

- The harness strips the `localStorage` boot script and bakes the chosen
  `data-design` / `dark` straight onto `<html>`, and rewrites the
  `themes.css` href to `file:///app/templates/themes.css` (the repo is
  mounted at `/app` in the container).
- It uses mock content, not the live DB, so it exercises the shell and
  themes — not page-specific query logic.
