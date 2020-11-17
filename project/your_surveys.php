<?php require_once(__DIR__ . "/partials/nav.php"); ?>
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
$stmt = $db->prepare("SELECT id, title, description, visibility, user_id FROM Survey WHERE user_id = :uid LIMIT 10");
$r = $stmt->execute([":uid" => $user_id]);
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
                        <div>Title: <?php safer_echo($r["title"]); ?></div>
                    </div>
                    <div>
                        <div>Description:
                            <?php
                            if(strlen($r["description"]) > 50) {
                                safer_echo(substr($r["description"], 0, 50) . "...");
                            }
                            else {
                                safer_echo($r["description"]);
                            }
                            ?>
                        </div>
                    </div>
                    <div>
                        <div>Visibility: <?php get_visibility($r["visibility"]); ?></div>
                    </div>
                    <div>
                        <div>Owner ID: <?php get_visibility($r["user_id"]); ?></div>
                    </div>
                    <div>
                        <a type="button" class="btn btn-primary" href="<?php echo get_url("test/test_edit_survey.php"); ?>?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <a type="button" class="btn btn-primary" href="<?php echo get_url("test/test_view_survey.php"); ?>?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
