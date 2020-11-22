CREATE TABLE Answers
(
    id          int auto_increment,
    answer      varchar(100) NOT NULL,
    created     TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
    modified    TIMESTAMP NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    question_id int,
    primary key (id),
    FOREIGN KEY (question_id) REFERENCES Questions (id) ON DELETE SET NULL
)
