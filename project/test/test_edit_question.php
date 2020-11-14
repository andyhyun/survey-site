<?php require_once(__DIR__ . "/../partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: ../login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//saving
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $question = $_POST["question"];
    $survey = $_POST["survey_id"];
    if ($survey <= 0) {
        $survey = null;
    }
    $user = get_user_id();
    $db = getDB();
    if(isset($id)) {
        $stmt = $db->prepare("UPDATE Questions SET question=:question, survey_id=:survey WHERE id=:id");
        $r = $stmt->execute([
            ":question" => $question,
            ":survey" => $survey,
            ":id" => $id
        ]);
        if ($r) {
            flash("Updated successfully with id: " . $id);
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error updating: " . var_export($e, true));
        }
    }
    else {
        flash("ID isn't set, we need an ID in order to update");
    }
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Questions WHERE id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
//get surveys for dropdown
$db = getDB();
$stmt = $db->prepare("SELECT id, title FROM Survey LIMIT 10");
$r = $stmt->execute();
$surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <h3>Edit Question</h3>
    <form method="POST">
        <label>Question</label>
        <input name="question" placeholder="Question" value="<?php echo $result["question"]; ?>" required maxlength="125"/>
        <label>Survey</label>
        <select name="survey_id" value="<?php echo $result["survey_id"];?>" >
            <option value="-1">None</option>
            <?php foreach ($surveys as $survey): ?>
                <option value="<?php safer_echo($survey["id"]); ?>" <?php echo ($result["survey_id"] == $survey["id"] ? 'selected="selected"' : ''); ?>
                ><?php safer_echo($survey["title"]); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" name="save" value="Update"/>
    </form>


<?php require(__DIR__ . "/../partials/flash.php");