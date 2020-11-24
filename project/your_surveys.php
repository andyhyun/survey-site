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
    <h3>Your Latest Surveys</h3>
    <?php if (count($results) > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Description</th>
                    <th scope="col">Category</th>
                    <th scope="col">Visibility</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $r): ?>
                <tr>
                    <td><?php safer_echo($r["title"]) ?></td>
                    <td>
                        <?php
                        if(strlen($r["description"]) > 50) {
                            safer_echo(substr($r["description"], 0, 50) . "...");
                        }
                        else {
                            safer_echo($r["description"]);
                        }
                        ?>
                    </td>
                    <td><?php safer_echo($r["category"]) ?></td>
                    <td><?php get_visibility($r["visibility"]) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
