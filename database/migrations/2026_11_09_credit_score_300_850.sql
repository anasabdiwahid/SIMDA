-- Credit score back to the brief's 300-850 scale: new clients start at 600 (formula in README section 4).
-- Run once on an existing database, then recalculate every client's score:
--   C:\xampp\php\php.exe database/recalculate_scores.php
ALTER TABLE credit_scores
    MODIFY current_score INT NOT NULL DEFAULT 600,
    MODIFY previous_score INT NOT NULL DEFAULT 600;
