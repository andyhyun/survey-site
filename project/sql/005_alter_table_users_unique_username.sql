--Added a unique constraint for username
ALTER TABLE Users
    ADD UNIQUE (username);
