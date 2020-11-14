<?php require_once(__DIR__ . "/../partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: ../login.php"));
}
?>
<?php
$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Questions.id, Questions.question, Survey.title AS survey FROM Questions LEFT JOIN Survey on Questions.survey_id = Survey.id WHERE Questions.question like :q LIMIT 10");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }
}
?>
<h3>List Questions</h3>
<form method="POST">
    <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
    <input type="submit" value="Search" name="search"/>
</form>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Question:</div>
                        <div><?php safer_echo($r["question"]); ?></div>
                    </div>
                    <div>
                        <div>Survey:</div>
                        <div><?php safer_echo($r["survey"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="test_edit_question.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <a type="button" href="test_view_question.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/../partials/flash.php"); ?>