<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if(!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>
<?php
if (isset($_GET["id"])) {
    $sid = $_GET["id"];
    $db = getDB();
    // May need to make this query shorter
    $stmt = $db->prepare("SELECT qryone.*, qrytwo.q_responses FROM (SELECT q.id as GroupId, q.id as QuestionId, q.question, s.id as SurveyId, s.title as SurveyTitle, s.description, s.category, s.visibility, s.user_id, a.id as AnswerId, a.answer, COUNT(r.id) as times_chosen 
                          FROM Surveys as s JOIN Questions as q on s.id = q.survey_id JOIN Answers as a on a.question_id = q.id LEFT JOIN Responses as r ON r.chosen_answer_id = a.id GROUP BY a.id) as qryone 
                          LEFT JOIN (SELECT QuestionId, SUM(times_chosen) as q_responses FROM (SELECT q.id as GroupId, q.id as QuestionId, COUNT(r.id) as times_chosen FROM Surveys as s JOIN Questions as q on s.id = q.survey_id 
                          JOIN Answers as a on a.question_id = q.id LEFT JOIN Responses as r ON r.chosen_answer_id = a.id GROUP BY a.id) as qryoneclone GROUP BY QuestionId) as qrytwo ON qryone.QuestionId = qrytwo.QuestionId 
                          HAVING SurveyId = :survey_id");
    $r = $stmt->execute([":survey_id" => $sid]);
    $title = "";
    $description = "";
    $category = "";
    $questions = [];
    $answer_percentage = 0;
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_GROUP);
        if ($results) {
            foreach ($results as $index => $group) {
                foreach ($group as $details) {
                    if($details["visibility"] == 0 && get_user_id() != $details["user_id"]) {
                        flash("You don't have permission to access this page");
                        die(header("Location: public_surveys.php"));
                    }
                    if (empty($title)) {
                        $title = $details["SurveyTitle"];
                    }
                    if (empty($description)) {
                        $description = $details["description"];
                    }
                    if (empty($category)) {
                        $category = $details["category"];
                    }
                    $qid = $details["QuestionId"];
                    if($details["q_responses"] == 0) {
                        $answer_percentage = 0;
                    }
                    else {
                        $answer_percentage = 100 * (round(($details["times_chosen"]/$details["q_responses"]), 3));
                    }
                    $answer = ["answerId" => $details["AnswerId"], "answer" => $details["answer"], "answer_percentage" => $answer_percentage];
                    if (!isset($questions[$qid]["answers"])) {
                        $questions[$qid]["question"] = $details["question"];
                        $questions[$qid]["answers"] = [];
                    }
                    array_push($questions[$qid]["answers"], $answer);
                }
            }
        }
        else {
            flash("This survey does not exist");
            die(header("Location: public_surveys.php"));
        }
    }
    else {
        flash("There was a problem fetching the survey");
        die(header("Location: public_surveys.php"));

    }
}
else {
    flash("The requested survey could not be found");
    die(header("Location: public_surveys.php"));
}
?>
<div class="container-fluid">
    <h3 style="margin-top: 20px;margin-bottom: 20px;"><?php safer_echo("Results of " . $title); ?></h3>
    <p><?php safer_echo($description); ?></p>
    <div class="list-group">
        <?php foreach ($questions as $index => $question): ?>
            <div class="list-group-item">
                <div><?php safer_echo($question["question"]); ?></div>
                <div>
                    <div>
                        <?php foreach ($question["answers"] as $answer): ?>
                            <?php $eleId = $index . '-' . $answer["answerId"]; ?>
                            <div name="<?php safer_echo($index); ?>" id="option-<?php echo $eleId; ?>">
                                <?php safer_echo($answer["answer"] . "  |  " . $answer["answer_percentage"] . "%"); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>