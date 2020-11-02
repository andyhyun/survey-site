<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if(!has_role("Admin")) {
    // Will redirect the user to login if they are not an admin, and the rest of the script is killed
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php
if(isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>

<?php
// saving
if(isset($_POST["save"])) {
    // TODO: add proper validation/checks
    $title = $_POST["title"];
    $description = $_POST["description"];
    $visibility = $_POST["visibility"];
    $user = get_user_id();
    $db = getDB();
    if(isset($id)) {
        $stmt = $db->prepare("UPDATE Survey set title=:title, description=:description, visibility=:visibility where id=:id");
        $r = $stmt->execute([
            ":title"=>$title,
            ":description"=>$description,
            ":visibility"=>$visibility,
            ":id"=>$id
        ]);
        if($r) {
            flash("Updated successfully with id: " . $id);
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error updating: " . var_export($e, true));
        }
    }
    else {
        flash("ID isn't set, we need an ID in order to update");
    }
}
?>

<?php
// fetching
$result = [];
if(isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Survey where id = :id");
    $r = $stmt->execute([":id"=>$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
    <label>Title</label>
    <input name="title" maxlength="30" value="<?php echo $result["title"];?>"/>
    <label>Description</label>
    <input name="description" value="<?php echo $result["description"];?>"/>
    <label>Visibility</label>
    <select name="visibility" value="<?php echo $result["visibility"];?>">
        <option value="0" <?php echo ($result["visibility"] == "0"?'selected="selected"':'');?>>Draft</option>
        <option value="1" <?php echo ($result["visibility"] == "1"?'selected="selected"':'');?>>Private</option>
        <option value="2" <?php echo ($result["visibility"] == "2"?'selected="selected"':'');?>>Public</option>
    </select>
    <input type="submit" name="save" value="Update"/>
</form>

<?php require(__DIR__ . "/partials/flash.php"); ?>
