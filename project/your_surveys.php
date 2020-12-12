<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if(!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$user_id = get_user_id();

$query = "SELECT COUNT(1) AS total FROM Surveys WHERE user_id = :uid";
$params = [":uid" => $user_id];
$per_page = 10;
paginate($query, $params, $per_page);

$results = [];
$db = getDB();
$stmt = $db->prepare("SELECT DISTINCT s.*, u.username, (SELECT COUNT(DISTINCT user_id) FROM Responses r WHERE r.survey_id = s.id) AS total FROM Surveys s JOIN Users u ON s.user_id = u.id 
                      LEFT JOIN Responses r ON s.id = r.survey_id WHERE s.user_id = :uid ORDER BY created DESC LIMIT :offset, :count");
// $r = $stmt->execute([":uid" => $user_id]);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":uid", get_user_id());
$r = $stmt->execute();
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the results");
}
$nav_label = "Your Created Surveys";
?>

<div class="container-fluid">
    <h3 style="margin-top: 20px;margin-bottom: 20px;">Your Latest Surveys</h3>
    <?php include(__DIR__."/partials/pagination.php");?>
    <div class="list-group">
        <?php if($results && count($results) > 0): ?>
            <div class="list-group-item" style="background-color: #e8faff;">
                <div class="row">
                    <div class="col-3">Title (Click to Take Survey)</div>
                    <div class="col-3">Description</div>
                    <div class="col-1" align="center">Category</div>
                    <div class="col-1" align="center">Visibility</div>
                    <div class="col-3" align="center">Posted By</div>
                    <div class="col-1" align="center">Options</div>
                </div>
            </div>
            <?php foreach($results as $r): ?>
                <div class="list-group-item">
                    <div class="row">
                        <div class="col-3"><a href="<?php echo get_url("survey.php?id=" . $r["id"]); ?>"><?php safer_echo($r["title"]) ?></a></div>
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
                        <div class="col-1" align="center"><?php get_visibility($r["visibility"]) ?></div>
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
                You don't have any surveys yet!
            </div>
        <?php endif; ?>
    </div>
    <?php include(__DIR__."/partials/pagination.php");?>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
