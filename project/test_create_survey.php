<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if(!has_role("Admin")) {
    // Will redirect the user to login if they are not an admin, and the rest of the script is killed
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<form method="POST">
    <label>Title</label>
    <input name="title" required maxlength="30"/>
    <label>Description</label>
    <input name="description"/>
    <label>Visibility</label>
    <select name="visibility">
        <option value="0">Draft</option>
        <option value="1">Private</option>
        <option value="2">Public</option>
    </select>
    <input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])) {
    // TODO: add proper validation/checks
    $title = $_POST["title"];
    $description = $_POST["description"];
    $visibility = $_POST["visibility"];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Survey (title, description, visibility, user_id) VALUES (:title, :description, :visibility, :user)");
    $r = $stmt->execute([
        ":title"=>$title,
        ":description"=>$description,
        ":visibility"=>$visibility,
        ":user"=>$user
    ]);
    if($r) {
        flash("Created successfully with id: " . $db->lastInsertID());
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php require(__DIR__ . "/partials/flash.php"); ?>
