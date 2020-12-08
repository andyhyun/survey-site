<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if(!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$title_filter = "";
$category_filter = "";
$results = [];
if(isset($_POST["title_filter"])) {
    $title_filter = $_POST["title_filter"];
}
if(isset($_POST["category_filter"])) {
    $category_filter = $_POST["category_filter"];
}
if(isset($_POST["search"])) {
    $db = getDB();
    $stmt = $db->prepare("SELECT title, description, category, username, Surveys.id FROM Surveys JOIN Users ON Surveys.user_id = Users.id WHERE title LIKE :tf AND category LIKE :cf AND visibility = 2 ORDER BY Surveys.created DESC LIMIT 10");
    $r = $stmt->execute([":tf" => "%$title_filter%", ":cf" => "%$category_filter%"]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}
else {
    $db = getDB();
    $stmt = $db->prepare("SELECT title, description, category, username, Surveys.id FROM Surveys JOIN Users ON Surveys.user_id = Users.id WHERE visibility = 2 ORDER BY Surveys.created DESC LIMIT 10");
    $r = $stmt->execute();
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}
?>
<div class="container-fluid">
    <form method="POST">
        <h3 style="margin-top: 20px;margin-bottom: 20px;">Search Surveys</h3>
        <div class="form-group">
            <input class="form-control" name="title_filter" placeholder="Title" maxlength="45" value="<?php safer_echo($title_filter); ?>"/>
        </div>
        <div class="form-group">
            <input class="form-control" name="category_filter" placeholder="Category" maxlength="15" value="<?php safer_echo($category_filter); ?>"/>
        </div>
        <input class="btn btn-primary" type="submit" value="Search" name="search"/>
    </form>
</div>

<div class="container-fluid">
    <div class="list-group">
        <?php if($results && count($results) > 0): ?>
            <div class="list-group-item" style="background-color: #e8faff;">
                <div class="row">
                    <div class="col-4">Title</div>
                    <div class="col-3">Description</div>
                    <div class="col-2" align="center">Category</div>
                    <div class="col-3" align="center">Posted By</div>
                    <div class="col-1" align="center">Options</div>
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
                        <div class="col-2" align="center"><?php safer_echo($r["category"]) ?></div>
                        <div class="col-3" align="center"><?php safer_echo($r["username"]) ?></div>
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
                No results 
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
