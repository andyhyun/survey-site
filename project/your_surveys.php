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
$stmt = $db->prepare("SELECT id, title, description, category, visibility FROM Surveys WHERE user_id = :uid ORDER BY created LIMIT 10");
$r = $stmt->execute([":uid" => $user_id]);
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the results");
}
?>

<div class="container-fluid">
    <h3 style="margin-top: 20px;margin-bottom: 20px;">Your Latest Surveys</h3>
    <div class="list-group">
        <?php if($results && count($results) > 0): ?>
            <div class="list-group-item" style="background-color: #e8faff;">
                <div class="row">
                    <div class="col-3">Title</div>
                    <div class="col-5">Description</div>
                    <div class="col-2">Category</div>
                    <div class="col-2">Visibility</div>
                </div>
            </div>
            <?php foreach($results as $r): ?>
                <div class="list-group-item">
                    <div class="row">
                        <div class="col-3"><?php safer_echo($r["title"]) ?></div>
                        <div class="col-5">
                            <?php
                            if(strlen($r["description"]) > 50) {
                                safer_echo(substr($r["description"], 0, 50) . "...");
                            }
                            else {
                                safer_echo($r["description"]);
                            }
                            ?>
                        </div>
                        <div class="col-2"><?php safer_echo($r["category"]) ?></div>
                        <div class="col-2"><?php get_visibility($r["visibility"]) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else:?>
            <div class="list-group-item">
                You don't have any surveys yet!
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
