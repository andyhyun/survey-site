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
$stmt = $db->prepare("SELECT DISTINCT s.*, u.username, r.created AS r_created, (SELECT COUNT(DISTINCT user_id) FROM Responses WHERE Responses.survey_id = s.id) AS total 
                      FROM Surveys s JOIN Users u ON s.user_id = u.id JOIN Responses r ON s.id = r.survey_id WHERE r.user_id = :uid ORDER BY r_created DESC");
$r = $stmt->execute([":uid" => $user_id]);
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the results");
}
?>

<div class="container-fluid">
    <h3 style="margin-top: 20px;margin-bottom: 20px;">Surveys that You've Taken</h3>
    <div class="list-group">
        <?php if($results && count($results) > 0): ?>
            <div class="list-group-item" style="background-color: #e8faff;">
                <div class="row">
                    <div class="col-4">Title</div>
                    <div class="col-3">Description</div>
                    <div class="col-1" align="center">Category</div>
                    <div class="col-3" align="center">Posted By</div>
                    <div class="col-1" align="center"></div>
                </div>
            </div>
            <?php foreach($results as $r): ?>
                <div class="list-group-item">
                    <div class="row">
                        <div class="col-4"><?php safer_echo($r["title"]) ?></div>
                        <div class="col-3">
                            <?php
                            if(strlen($r["description"]) > 40) {
                                safer_echo(substr($r["description"], 0, 37) . "...");
                            }
                            else {
                                safer_echo($r["description"]);
                            }
                            ?>
                        </div>
                        <div class="col-1" align="center"><?php safer_echo($r["category"]) ?></div>
                        <div class="col-3" align="center"><a href="<?php echo get_url("profile.php?id=" . $r["user_id"]); ?>"><?php safer_echo($r["username"]) ?></a></div>
                        <div class="col-1" align="center">
                            <a href="<?php echo get_url("results.php?id=" . $r["id"]); ?>" class="btn btn-primary" role="button">Results</a>
                            <div style="padding-top: 10px;">
                                <?php
                                if($r["total"] == 1) {
                                    safer_echo("Taken 1 Time");
                                }
                                else {
                                    safer_echo("Taken " . $r["total"] . " Times");
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else:?>
            <div class="list-group-item">
                You haven't taken any surveys yet!
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
