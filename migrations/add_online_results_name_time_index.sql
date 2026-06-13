-- Speeds up the per-character chart range query (getData.php) and the
-- online stats page. online_results has ~2.3M rows; the prior single-column
-- (name) index forced a scan of all of a character's rows for any date range.
-- A composite (name, online_time) makes the range lookup an index range scan.
--
-- NOTE: already applied to production on 2026-06-13.
ALTER TABLE online_results ADD INDEX idx_name_online_time (name, online_time);
