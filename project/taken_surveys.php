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
$stmt = $db->prepare("SELECT DISTINCT s.id, s.title, s.description, s.category, s.visibility, r.user_id, r.created FROM Surveys s JOIN Responses r ON s.id = r.survey_id WHERE r.user_id = :uid ORDER BY r.created DESC LIMIT 10");
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
                    <div class="col-4">Description</div>
                    <div class="col-1" align="center">Category</div>
                    <div class="col-2" align="center">Visibility</div>
                    <div class="col-1" align="center">Options</div>
                </div>
            </div>
            <?php foreach($results as $r): ?>
                <div class="list-group-item">
                    <div class="row">
                        <div class="col-4"><?php safer_echo($r["title"]) ?></div>
                        <div class="col-4">
                            <?php
                            if(strlen($r["description"]) > 50) {
                                safer_echo(substr($r["description"], 0, 47) . "...");
                            }
                            else {
                                safer_echo($r["description"]);
                            }
                            ?>
                        </div>
                        <div class="col-1" align="center"><?php safer_echo($r["category"]) ?></div>
                        <div class="col-2" align="center"><?php get_visibility($r["visibility"]) ?></div>
                        <div class="col-1 btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="<?php echo get_url("survey.php?id=" . $r["id"]); ?>">Take Survey</a>
                                <a class="dropdown-item" href="<?php echo get_url("results.php?id=" . $r["id"]); ?>">View Results</a>
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
