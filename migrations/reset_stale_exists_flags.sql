-- One-time repair of `exists` flags that latched to 0 and never came back.
--
-- scrape.php only ever wrote `exists` = 0 (profile lookup returned "There is no
-- player named"); no code path restored it to 1, so a single transient miss hid
-- a character from search_characters.php forever. The scraper now clears the
-- flag whenever it sees a character online or parses their profile, but rows
-- already stuck at 0 need this one-off reset.
--
-- Each of the 18 flagged characters was re-checked against
-- fossil-legacy.com/characterprofile.php on 2026-08-29: these three returned a
-- normal profile page, the other 15 are genuinely deleted and stay flagged.
UPDATE
    character_vocations
SET
    `exists` = 1
WHERE
    name IN ('REDACTED', 'REDACTED', 'REDACTED');
