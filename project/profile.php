<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();
//save data if we submitted the form
if (isset($_POST["saved"])) {
    $isValid = true;
    //check if our email changed
    $oldEmail = get_email();
    $newEmail = get_email();
    if (get_email() != $_POST["email"]) {
        //TODO we'll need to check if the email is available
        $email = $_POST["email"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where email = :email");
        $stmt->execute([":email" => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Email is already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newEmail = $email;
        }
    }
    $oldUsername = get_username();
    $newUsername = get_username();
    if (get_username() != $_POST["username"]) {
        $username = $_POST["username"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where username = :username");
        $stmt->execute([":username" => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Username is already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newUsername = $username;
        }
    }
    if ($isValid) {
        if (($oldEmail != $newEmail) || ($oldUsername != $newUsername)) {
            $stmt = $db->prepare("UPDATE Users set email = :email, username= :username where id = :id");
            $r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":id" => get_user_id()]);
            if ($r) {
                flash("Updated profile");
            }
            else {
                flash("Error updating profile");
            }
        }
        else {
            // maybe put a message saying that nothing changed
        }
        //password is optional, so check if it's even set
        //if so, then check if it's a valid reset request
        if (!empty($_POST["password"]) && !empty($_POST["confirm"])) {
            if (empty($_POST["existing"])) {
                flash("Enter your existing password to reset password");
            }
            elseif ($_POST["password"] == $_POST["confirm"]) {
                $stmt = $db->prepare("SELECT password from Users WHERE email = :email LIMIT 1");
                $stmt->execute([":email" => $newEmail]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if($result && isset($result["password"])) {
                    $password_hash_from_db = $result["password"];
                    $existing = $_POST["existing"];
                    if(password_verify($existing, $password_hash_from_db)) {
                        $password = $_POST["password"];
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        //this one we'll do separate
                        $stmt = $db->prepare("UPDATE Users set password = :password where id = :id");
                        $r = $stmt->execute([":id" => get_user_id(), ":password" => $hash]);
                        if ($r) {
                            flash("Reset password");
                        }
                        else {
                            flash("Error resetting password");
                        }
                    }
                    else {
                        flash("The existing password you entered is incorrect. Please try again.");
                    }
                    unset($result["password"]);
                }
            }
            else {
                flash("Passwords do not match");
            }
        }
//fetch/select fresh data in case anything changed
        $stmt = $db->prepare("SELECT email, username from Users WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => get_user_id()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email = $result["email"];
            $username = $result["username"];
            //let's update our session too
            $_SESSION["user"]["email"] = $email;
            $_SESSION["user"]["username"] = $username;
        }
    }
    else {
        //else for $isValid, though don't need to put anything here since the specific failure will output the message
    }
}
?>
<div class="container-fluid">
    <form method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input class="form-control" type="email" name="email" value="<?php safer_echo(get_email()); ?>"/>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input class="form-control" type="text" maxlength="60" name="username" value="<?php safer_echo(get_username()); ?>"/>
        </div>
        <div class="form-group">
            <label for = "epw">Existing Password</label>
            <input class="form-control" type="password" maxlength="60" name="existing"/>
        </div>
        <div class="form-group">
            <label for="pw">Password</label>
            <input class="form-control" type="password" maxlength="60" name="password"/>
        </div>
        <div class="form-group">
            <label for="cpw">Confirm Password</label>
            <input class="form-control" type="password" maxlength="60" name="confirm"/>
        </div>
        <input class="btn btn-primary" type="submit" name="saved" value="Save Profile"/>
    </form>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
