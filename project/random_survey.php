<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if(!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$sid = 0;
$result = [];
$db = getDB();
$stmt = $db->prepare("SELECT * FROM (SELECT * FROM Surveys WHERE id NOT IN (SELECT DISTINCT survey_id FROM Responses WHERE user_id = :uid) AND visibility = 2) untaken_surveys ORDER BY RAND() LIMIT 1");
$r = $stmt->execute([":uid" => get_user_id]);
if($r) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
else {
    flash("There was an issue fetching the results");
    die(header("Location: public_surveys.php"));
}
if(count($result) > 0) {
    $sid = $result["id"];
    flash("Found a random survey!");
    die(header("Location: survey.php?id=$sid"));
}
else {
    flash("There are no more surveys left to take!");
    die(header("Location: public_surveys.php"));
}
?>