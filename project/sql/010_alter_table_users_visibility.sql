ALTER TABLE `Users`
    ADD COLUMN `acct_visibility` TINYINT NOT NULL DEFAULT 1; -- Private 0, Public 1