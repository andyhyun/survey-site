<?php require_once(__DIR__ . "/../partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: ../login.php"));
}
?>
<?php
if (isset($_GET["survey_id"])) {
    $survey = $_GET["survey_id"];
}
?>
    <h3>Create Question</h3>
    <form method="POST">
        <label>Question</label>
        <input name="question" placeholder="Question" required maxlength="125"/>
        <input type="submit" name="save" value="Create"/>
    </form>

<?php
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $question = $_POST["question"];
    $user = get_user_id();
    $db = getDB();
    if(isset($survey)) {
        $stmt = $db->prepare("INSERT INTO Questions (question, survey_id) VALUES (:question, :survey)");
        $r = $stmt->execute([
            ":question" => $question,
            ":survey" => $survey
        ]);
        if ($r) {
            flash("Created successfully with id: " . $db->lastInsertId());
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error creating: " . var_export($e, true));
        }
    }
    else {
        flash("Survey ID isn't set, we need a survey ID in order to create.");
    }
}
?>
<?php require(__DIR__ . "/../partials/flash.php");
