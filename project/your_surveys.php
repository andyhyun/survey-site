<?php require_once(__DIR__ . "/../partials/nav.php"); ?>
<?php
if(!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$results = [];
$user_id = get_user_id();
$db = getDB();
$stmt = $db->prepare("SELECT id, title, description, visibility FROM Survey WHERE id = 1");
$r = $stmt->execute();
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the results");
}
?>
<h3>Your Surveys</h3>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Title:</div>
                        <div><?php safer_echo($r["title"]); ?></div>
                    </div>
                    <div>
                        <div>Description:</div>
                        <div>
                        <?php
                        if(strlen($r["description"]) > 30) {
                            safer_echo(substr($r["description"], 0, 30) . "...");
                        }
                        else {
                            safer_echo($r["description"]);
                        }
                        ?>
                        </div>
                    </div>
                    <div>
                        <div>Visibility:</div>
                        <div><?php get_visibility($r["visibility"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="test_edit_survey.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <a type="button" href="test_view_survey.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/../partials/flash.php"); ?>
