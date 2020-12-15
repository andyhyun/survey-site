<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if(!has_role("Admin")) {
    flash("You don't have permission to access this page");
    die(header("Location: home.php"));
}

if(isset($_GET["id"])) {
    $id = $_GET["id"];
}
else {
    flash("The requested survey could not be found");
    die(header("Location: public_surveys.php"));
}

if(isset($_POST["saved"])) {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $visibility = $_POST["visibility"];
    $db = getDB();
    if(isset($id)) {
        $stmt->prepare("UPDATE Surveys SET title=:title, description=:description, category=:category, visibility=:visibility WHERE id=:id");
        $r = $stmt->execute([
            ":title" => $title,
            ":description" => $description,
            ":category" => $category,
            ":visibility" => $visibility,
            ":id"=>$id
        ]);
        if($r) {
            flash("Survey updated successfully");
        }
        else {
            flash("Survey could not be updated");
        }
    }
    else {
        flash("The requested survey could not be found");
        die(header("Location: public_surveys.php"));
    }
}

$result = [];
if(isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Surveys where id = :id");
    $r = $stmt->execute([":id"=>$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
else {
    flash("The requested survey could not be found");
    die(header("Location: public_surveys.php"));
}
?>

<div class="container-fluid">
    <h3 style="margin-top: 20px;margin-bottom: 20px;">Edit Survey</h3>
    <form method="POST">
        <div class="form-group">
            <label for="">Title</label>
            <input class="form-control" type="text" id="title" name="title" required maxlength="45" value="<?php echo $result["title"];?>"/>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" type="text" id="description" name="description" value="<?php echo $result["description"];?>"></textarea>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <input class="form-control" type="text" id="category" name="category" maxlength="15" value="<?php echo $result["category"];?>"/>
        </div>
        <div class="form-group">
            <label for="visibility">Visibility</label>
            <select class="form-control" name="visibility" id="visibility" required value="<?php echo $result["visibility"];?>">
                <option value="0" <?php echo ($result["visibility"] == "0"?'selected="selected"':'');?>>Draft</option>
                <option value="1" <?php echo ($result["visibility"] == "1"?'selected="selected"':'');?>>Private</option>
                <option value="2" <?php echo ($result["visibility"] == "2"?'selected="selected"':'');?>>Public</option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" style="margin-top: 30px;" name="saved" class="btn btn-primary" value="Update Survey"/>
        </div>
    </form>
</div>

<?php require(__DIR__ . "/partials/flash.php"); ?>