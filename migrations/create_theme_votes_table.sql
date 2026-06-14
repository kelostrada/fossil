-- Stores one row per (theme, anonymous voter). The unique key enforces
-- one vote per theme per voter; ip/user_agent are kept for spam review.
-- voter_id is the long-lived `fv_voter` cookie assigned on first visit.
--
-- NOTE: already applied to production on 2026-06-14.
CREATE TABLE IF NOT EXISTS theme_votes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    design      TINYINT NOT NULL,
    voter_id    VARCHAR(64) NOT NULL,
    ip          VARCHAR(45) DEFAULT NULL,
    user_agent  VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_design_voter (design, voter_id),
    KEY idx_design (design)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
