--Added usernmae column to Users table after creation
ALTER TABLE Users
    ADD COLUMN username varchar(60) default '';