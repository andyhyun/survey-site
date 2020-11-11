CREATE TABLE Questions
(
    id        int auto_increment,
    question  varchar(125),
    survey_id int,
    primary key (id),
    FOREIGN KEY (survey_id) REFERENCES Survey (id) ON DELETE SET NULL
)
