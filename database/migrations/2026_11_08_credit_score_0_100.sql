-- Credit score moves to a 0-100 scale. New clients start at 0 with category 'new'.
ALTER TABLE credit_scores
    MODIFY current_score INT NOT NULL DEFAULT 0,
    MODIFY previous_score INT NOT NULL DEFAULT 0,
    MODIFY score_category ENUM('new', 'excellent', 'good', 'fair', 'caution', 'high_risk') NOT NULL DEFAULT 'new';
-- Existing scores are recalculated by the app (CreditScoreEngine::recalculateForClient) after this runs.
