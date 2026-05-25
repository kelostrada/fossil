# Fossil Stats

A PHP-based statistics tracker and web frontend for the [Fossil](http://fossil-legacy.com/) private game server. It scrapes data from the official server site, stores it in MySQL, and exposes a Tailwind-styled dashboard for browsing highscores, online presence, deaths, and player activity over time.

Live instance: <https://fossil.kelostrada.pl/>

## Features

- **Online tracker** — current online players with levels, plus historical online time
- **Highscores** — per-skill, per-vocation leaderboards (Level, Magic, Club, Sword, Axe, Distance, Fist, Shield, Fishing)
- **Character profiles & charts** — skill progression over time for individual characters
- **Recent advancements** — newly raised skills across all tracked players
- **Recent deaths** — latest deaths with killer and level
- **Player killers** — leaderboard of PvP kill counts
- **Environmental killers** — leaderboard of most lethal monsters/sources
- **Character search** — autocomplete-powered character lookup
- **Discord notifications** — webhook alerts when watched players log in (with cooldown)

## Tech Stack

- **Backend:** PHP (mysqli), [`simple_html_dom`](simple_html_dom.php) for scraping
- **Database:** MySQL / MariaDB (InnoDB, utf8mb4)
- **Frontend:** Tailwind CSS, Alpine.js, jQuery UI (autocomplete)
- **Integrations:** Discord webhooks

## Repository Layout

```
.
├── index.php                  # Home / currently online
├── online.php                 # Online stats page
├── highscores.php             # Per-skill, per-vocation leaderboards
├── advancements.php           # Recent skill advancements
├── recent_deaths.php          # Latest deaths
├── playerkillers.php          # PvP kill leaderboard
├── environmental_killers.php  # Monster/source kill leaderboard
├── chart.php                  # Per-character progression chart
├── search_characters.php      # Autocomplete endpoint
├── scrape.php                 # Scraper entry point (highscores | online | profiles)
├── notify.php                 # Discord login notifier
├── getData.php                # Shared data helpers
├── config.php                 # .env loader, DB connection, Discord helper
├── templates/layout.php       # Shared page layout
├── migrations/                # SQL schema migrations
└── simple_html_dom.php        # Vendored HTML parser
```

## Setup

### Requirements

- PHP 7.4+ with `mysqli` and `curl`
- MySQL / MariaDB
- A web server (Apache, nginx + php-fpm, or `php -S` for local dev)

### Installation

1. Clone the repo:
   ```bash
   git clone git@github.com:kelostrada/fossil.git
   cd fossil
   ```

2. Copy the env sample and fill in your values:
   ```bash
   cp .env.sample .env
   ```

   ```ini
   DB_HOST=localhost
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   DB_DATABASE=your_database_name
   DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/...
   ```

3. Create the database and apply migrations:
   ```bash
   mysql -u <user> -p <db> < migrations/create_vocations_table.sql
   mysql -u <user> -p <db> < migrations/create_deaths_table.sql
   mysql -u <user> -p <db> < migrations/create_exists_column.sql
   ```

   You will also need a `scores` table and `online_results` table — see the `INSERT` statements in [`scrape.php`](scrape.php) for the expected schema (`scores(name, score, type, timestamp)`, `online_results(name, level, online_time)`).

4. Run locally:
   ```bash
   php -S localhost:8000
   ```
   Then visit <http://localhost:8000/>.

## Scraping

`scrape.php` accepts a `?type=` query parameter and is intended to be called on a schedule (e.g. cron / systemd timer hitting the URL):

| Type         | What it does                                                                |
|--------------|-----------------------------------------------------------------------------|
| `online`     | Snapshots the current online list, updates vocations and level scores       |
| `highscores` | Walks the highscores tables one page/skill per call (state in `page.txt` / `type.txt`) |
| `profiles`   | Fetches one character profile per call (state in `last_fetched_id.txt`), records vocation, last login, and deaths |

Example cron entries:

```cron
* * * * * curl -s "https://your-host/scrape.php?type=online" > /dev/null
* * * * * curl -s "https://your-host/scrape.php?type=highscores" > /dev/null
* * * * * curl -s "https://your-host/scrape.php?type=profiles" > /dev/null
*/5 * * * * php /path/to/fossil/notify.php
```

## Discord Notifications

`notify.php` checks the `online_results` table for a hard-coded list of watched players and posts to the Discord webhook when one logs in. A 6-hour per-player cooldown is tracked in `notified_logins.json`. Edit `$watchedPlayers` in [`notify.php`](notify.php) to change the list.

## License

No license specified — all rights reserved by the author.
