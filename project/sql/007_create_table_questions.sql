CREATE TABLE Questions
(
    id          int auto_increment,
    question    varchar(100) NOT NULL,
    created     TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
    modified    TIMESTAMP NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    survey_id   int,
    primary key (id),
    FOREIGN KEY (survey_id) REFERENCES Surveys (id) ON DELETE SET NULL
)
