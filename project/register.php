<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (isset($_POST["register"])) {
    $email = null;
    $password = null;
    $confirm = null;
    $username = null;
    if (isset($_POST["email"])) {
        $email = $_POST["email"];
    }
    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }
    if (isset($_POST["confirm"])) {
        $confirm = $_POST["confirm"];
    }
    if (isset($_POST["username"])) {
        $username = $_POST["username"];
    }
    $isValid = true;
    //check if passwords match on the server side
    if ($password == $confirm) {
        // echo "Passwords match <br>";
        // We don't have to show anything here
    }
    else {
        flash("Passwords don't match");
        $isValid = false;
    }
    // Way of checking if a username is in the form of an email comes from:
    // https://stackoverflow.com/questions/12026842/how-to-validate-an-email-address-in-php
    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
        flash("Username cannot be an email");
        $isValid = false;
    }
    if (!isset($email) || !isset($password) || !isset($confirm)) {
        $isValid = false;
    }
    //TODO other validation as desired, remember this is the last line of defense
    if ($isValid) {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $db = getDB();
        if (isset($db)) {
            //here we'll use placeholders to let PDO map and sanitize our data
            $stmt = $db->prepare("INSERT INTO Users(email, username, password) VALUES(:email,:username, :password)");
            //here's the data map for the parameter to data
            $params = array(":email" => $email, ":username" => $username, ":password" => $hash);
            $r = $stmt->execute($params);
            //let's just see what's returned
            //echo "db returned: " . var_export($r, true);
            $e = $stmt->errorInfo();
            if ($e[0] == "00000") {
                flash("You successfully registered, please login");
            }
            else {
                if ($e[0] == "23000") {//code for duplicate entry
                    flash("Username or email is taken, please try again");
                }
                else {
                    flash("An error occured, please try again");
                }
            }
        }
    }
    else {
        flash("There was a validation issue");
    }
}
//safety measure to prevent php warnings
if (!isset($email)) {
    $email = "";
}
if (!isset($username)) {
    $username = "";
}
?>
<form method="POST">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required value="<?php safer_echo($email); ?>"/>
    <label for="user">Username:</label>
    <input type="text" id="user" name="username" required maxlength="60" value="<?php safer_echo($username); ?>"/>
    <label for="p1">Password:</label>
    <input type="password" id="p1" name="password" required maxlength="60"/>
    <label for="p2">Confirm Password:</label>
    <input type="password" id="p2" name="confirm" required/>
    <input type="submit" name="register" value="Register"/>
</form>
<?php require(__DIR__ . "/partials/flash.php"); ?>
