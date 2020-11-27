CREATE TABLE IF NOT EXISTS Responses
(
    id                  int auto_increment,
    survey_id           int,
    question_id         int,
    chosen_answer_id    int,
    modified            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update current_timestamp,
    created             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    user_id             int,
    primary key         (id),
    FOREIGN KEY         (user_id) REFERENCES Users (id) ON DELETE SET NULL,
    FOREIGN KEY         (question_id) REFERENCES Questions (id) ON DELETE SET NULL,
    FOREIGN KEY         (chosen_answer_id) REFERENCES Answers (id) ON DELETE SET NULL,
    FOREIGN KEY         (survey_id) REFERENCES Surveys (id) ON DELETE SET NULL,
    UNIQUE KEY          (user_id, question_id, survey_id)
)