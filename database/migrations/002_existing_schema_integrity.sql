ALTER TABLE guide_steps
    ADD CONSTRAINT uq_guide_steps_guide_number UNIQUE (guide_id, step_number);

ALTER TABLE guide_ratings
    ADD CONSTRAINT chk_guide_ratings_rating CHECK (rating BETWEEN 1 AND 5);

ALTER TABLE user_progress
    ADD CONSTRAINT chk_user_progress_completed CHECK (completed IN (0, 1));
