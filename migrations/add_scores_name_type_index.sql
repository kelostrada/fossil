-- Supports the "latest score per (name, type)" lookups used by highscores.php
-- and advancements.php (derived-table join and window-function partitioning).
--
-- NOTE: already applied to production on 2026-06-13.
ALTER TABLE scores ADD INDEX idx_name_type_timestamp (name, type, timestamp);
