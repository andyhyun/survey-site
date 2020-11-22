CREATE TABLE Surveys
(
    id          int auto_increment,
    title       varchar(45) NOT NULL,
    description text,
    category    varchar(15),
    visibility  int, -- Draft 0, Private 1, Public 2
    created     TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
    modified    TIMESTAMP NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    user_id     int,
    primary key (id),
    FOREIGN KEY (user_id) REFERENCES Users (id)
)