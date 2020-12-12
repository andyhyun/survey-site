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
    $stmt = $db->prepare("SELECT q.id AS group_id, q.id AS question_id, q.question, s.*, a.id AS answer_id, a.answer, u.username, (SELECT COUNT(chosen_answer_id) FROM Responses r WHERE r.chosen_answer_id = a.id) AS times_chosen, 
                          (SELECT COUNT(question_id) FROM Responses r WHERE r.question_id = q.id) AS q_responses FROM Questions q LEFT JOIN Answers a ON a.question_id = q.id 
                          JOIN Surveys s ON s.id = q.survey_id JOIN Users u ON s.user_id = u.id WHERE q.survey_id = :survey_id");
    $r = $stmt->execute([":survey_id" => $sid]);
    $title = "";
    $description = "";
    $creator_username = "";
    $creator_user_id = 0;
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
                        $title = $details["title"];
                    }
                    if (empty($description)) {
                        $description = $details["description"];
                    }
                    if (empty($creator_username)) {
                        $creator_username = $details["username"];
                    }
                    if (empty($creator_user_id)) {
                        $creator_user_id = $details["user_id"];
                    }
                    if (empty($category)) {
                        $category = $details["category"];
                    }
                    $qid = $details["question_id"];
                    if($details["q_responses"] == 0) { // have to make sure to not divide by zero 
                        $answer_percentage = 0;        // when calculating percentages
                    }
                    else {
                        $answer_percentage = 100 * (round(($details["times_chosen"]/$details["q_responses"]), 3));
                    }
                    $answer = ["answerId" => $details["answer_id"], "answer" => $details["answer"], "answer_percentage" => $answer_percentage];
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
        flash("There was a problem getting the results");
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
    <div>by <a href="<?php echo get_url("profile.php?id=" . $creator_user_id); ?>"><?php safer_echo($creator_username); ?></a></div>
    <p><?php safer_echo($description); ?></p>
    <div class="list-group">
        <?php foreach ($questions as $index => $question): ?>
            <div class="list-group-item">
                <h4><?php safer_echo($question["question"]); ?></h4>
                <div>
                    <div>
                        <?php foreach ($question["answers"] as $answer): ?>
                            <?php $eleId = $index . '-' . $answer["answerId"]; ?>
                            <div name="<?php safer_echo($index); ?>" id="option-<?php echo $eleId; ?>">
                                <?php safer_echo($answer["answer_percentage"] . "%  |  " . $answer["answer"]); ?>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php safer_echo((int)($answer["answer_percentage"])); ?>%;" 
                                    aria-valuenow="<?php safer_echo((int)($answer["answer_percentage"])); ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php safer_echo($answer["answer_percentage"] . "%"); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>