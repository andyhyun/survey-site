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

$id = get_user_id();
if(isset($_GET["id"])) {
    $id = $_GET["id"];
}

//save data if we submitted the form and if the user id in the url is the currently logged in user
if ($id == get_user_id() && isset($_POST["saved"])) {
    $db = getDB();
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
        $oldAcctVisibility = get_acct_visibility();
        $newAcctVisibility = $_POST["acct_visibility"];
        if (($oldEmail != $newEmail) || ($oldUsername != $newUsername) || ($oldAcctVisibility != $newAcctVisibility)) {
            $stmt = $db->prepare("UPDATE Users set email = :email, username= :username, acct_visibility = :acct_visibility where id = :id");
            $r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":acct_visibility" => $newAcctVisibility, ":id" => get_user_id()]);
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
        else if(empty($_POST["password"]) && !empty($_POST["confirm"]) || !empty($_POST["password"]) && empty($_POST["confirm"])) {
            flash("New passwords do not match");
        }
        //fetch/select fresh data in case anything changed
        $stmt = $db->prepare("SELECT email, username, acct_visibility from Users WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => get_user_id()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email = $result["email"];
            $username = $result["username"];
            $acct_visibility = $result["acct_visibility"];
            //let's update our session too
            $_SESSION["user"]["email"] = $email;
            $_SESSION["user"]["username"] = $username;
            $_SESSION["user"]["acct_visibility"] = $acct_visibility;
        }
    }
    else {
        //else for $isValid, though don't need to put anything here since the specific failure will output the message
    }
}

$db = getDB();
$stmt = $db->prepare("SELECT username, acct_visibility FROM Users WHERE id = :id LIMIT 1");
$r = $stmt->execute([":id" => $id]);
if($r) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
else {
    flash("This account does not exist");
    die(header("Location: public_surveys.php"));
}
$profile_data = [];
if($result["acct_visibility"] == 0 && $id != get_user_id()) {
    flash("That account is private");
    die(header("Location: public_surveys.php"));
}
else {
    $profile_data = $result; // $result may be used for other queries
}

$query = "SELECT COUNT(1) AS ytotal FROM Surveys WHERE user_id = :uid";
$params = [":uid" => $id];
$per_page = 10;
$ypage = 1;

if (isset($_GET["ypage"])) {
    try {
        $ypage = (int)$_GET["ypage"];
    }
    catch(Exception $e) {
        $ypage = 1;
    }
}
else {
    $ypage = 1;
}

$db = getDB();
$stmt = $db->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$ytotal = 0;
if($result) {
    $ytotal = (int)$result["ytotal"];
}

$ytotal_pages = ceil($ytotal / $per_page);
$yoffset = ($ypage - 1) * $per_page;

$yresults = [];
$db = getDB();
$stmt = $db->prepare("SELECT DISTINCT s.*, u.username, (SELECT COUNT(DISTINCT user_id) FROM Responses r WHERE r.survey_id = s.id) AS total FROM Surveys s JOIN Users u ON s.user_id = u.id 
                      LEFT JOIN Responses r ON s.id = r.survey_id WHERE s.user_id = :uid ORDER BY created DESC LIMIT :offset, :count");
// $r = $stmt->execute([":uid" => $user_id]);
$stmt->bindValue(":offset", $yoffset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":uid", $id);
$r = $stmt->execute();
if ($r) {
    $yresults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the results");
}

$query = "SELECT COUNT(DISTINCT user_id, survey_id) AS ttotal FROM Responses WHERE user_id = :uid";
$params = [":uid" => $id];
$per_page = 10;
$tpage = 1;

if (isset($_GET["tpage"])) {
    try {
        $tpage = (int)$_GET["tpage"];
    }
    catch(Exception $e) {
        $tpage = 1;
    }
}
else {
    $tpage = 1;
}

$db = getDB();
$stmt = $db->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$ttotal = 0;
if($result) {
    $ttotal = (int)$result["ttotal"];
}

$ttotal_pages = ceil($ttotal / $per_page);
$toffset = ($tpage - 1) * $per_page;

$tresults = [];
$db = getDB();
$stmt = $db->prepare("SELECT DISTINCT s.*, u.username, r.created AS r_created, (SELECT COUNT(DISTINCT user_id) FROM Responses WHERE Responses.survey_id = s.id) AS total 
                      FROM Surveys s JOIN Users u ON s.user_id = u.id JOIN Responses r ON s.id = r.survey_id WHERE r.user_id = :uid ORDER BY r_created DESC LIMIT :offset, :count");
// $r = $stmt->execute([":uid" => $user_id]);
$stmt->bindValue(":offset", $toffset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":uid", $id);
$r = $stmt->execute();
if ($r) {
    $tresults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the results");
}
?>

<div class="container-fluid">
    <h3 style="margin-top: 20px;margin-bottom: 20px;"><?php safer_echo($profile_data["username"]); ?></h3>
    <hr>
    <?php if($id == get_user_id()): ?>
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
            <label for="acct_visibility">Account Visibility</label>
                <select class="form-control" name="acct_visibility" id="acct_visibility" required>
                    <option value="0" <?php echo ($profile_data["acct_visibility"] == "0"?'selected="selected"':'');?>>Private</option>
                    <option value="1" <?php echo ($profile_data["acct_visibility"] == "1"?'selected="selected"':'');?>>Public</option>
                </select>
            </div>
            <hr>
            <div class="form-group">
                <label for="pw">New Password</label>
                <input class="form-control" type="password" maxlength="60" name="password"/>
            </div>
            <div class="form-group">
                <label for="cpw">Confirm New Password</label>
                <input class="form-control" type="password" maxlength="60" name="confirm"/>
            </div>
            <div class="form-group">
                <label for = "epw">Existing Password</label>
                <input class="form-control" type="password" maxlength="60" name="existing"/>
            </div>
            <input class="btn btn-primary" type="submit" name="saved" value="Save Profile"/>
        </form>
    <?php endif; ?>
    <hr>
    <h3 style="margin-top: 20px;margin-bottom: 20px;"><?php safer_echo($profile_data["username"] . "'s Latest Surveys"); ?></h3>
    <?php if(isset($ypage) && isset($ytotal_pages)):?>
        <nav aria-label="Your Created Surveys" style="margin: 40px;">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($ypage - 1) < 1?"disabled":"";?>">
                    <a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&ypage=<?php echo $ypage - 1;?>&tpage=<?php echo $tpage; ?>" tabindex="-1">Previous</a>
                </li>
                <?php for($i = 0; $i < $ytotal_pages; $i++):?>
                    <li class="page-item <?php echo ($ypage - 1) == $i?"active":"";?>"><a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&ypage=<?php echo ($i+1);?>&tpage=<?php echo $tpage; ?>"><?php echo ($i + 1);?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($ypage) >= $ytotal_pages?"disabled":"";?>">
                    <a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&ypage=<?php echo $ypage + 1;?>&tpage=<?php echo $tpage; ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif;?>
    <div class="list-group">
        <?php if($yresults && count($yresults) > 0): ?>
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
            <?php foreach($yresults as $r): ?>
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
                            <?php if(has_role("Admin")): ?>
                                <a href="<?php echo get_url("edit_survey.php?id=" . $r["id"]); ?>" class="btn btn-info" role="button" style="margin-top: 5px;">Edit</a>
                            <?php endif; ?>
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
                <?php safer_echo($profile_data["username"] . " doesn't have any surveys yet!"); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if(isset($ypage) && isset($ytotal_pages)):?>
        <nav aria-label="Your Created Surveys" style="margin: 40px;">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($ypage - 1) < 1?"disabled":"";?>">
                    <a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&ypage=<?php echo $ypage - 1;?>&tpage=<?php echo $tpage; ?>" tabindex="-1">Previous</a>
                </li>
                <?php for($i = 0; $i < $ytotal_pages; $i++):?>
                    <li class="page-item <?php echo ($ypage - 1) == $i?"active":"";?>"><a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&ypage=<?php echo ($i+1);?>&tpage=<?php echo $tpage; ?>"><?php echo ($i + 1);?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($ypage) >= $ytotal_pages?"disabled":"";?>">
                    <a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&ypage=<?php echo $ypage + 1;?>&tpage=<?php echo $tpage; ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif;?>
    <hr>
    <h3 style="margin-top: 20px;margin-bottom: 20px;"><?php safer_echo("Surveys that " . $profile_data["username"] . " Took"); ?></h3>
    <?php if(isset($tpage) && isset($ttotal_pages)):?>
        <nav aria-label="Taken Surveys" style="margin: 40px;">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($tpage - 1) < 1?"disabled":"";?>">
                    <a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&tpage=<?php echo $tpage - 1;?>&ypage=<?php echo $ypage; ?>" tabindex="-1">Previous</a>
                </li>
                <?php for($i = 0; $i < $ttotal_pages; $i++):?>
                    <li class="page-item <?php echo ($tpage - 1) == $i?"active":"";?>"><a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&tpage=<?php echo ($i+1);?>&ypage=<?php echo $ypage; ?>"><?php echo ($i + 1);?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($tpage) >= $ttotal_pages?"disabled":"";?>">
                    <a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&tpage=<?php echo $tpage + 1;?>&ypage=<?php echo $ypage; ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif;?>
    <div class="list-group">
        <?php if($tresults && count($tresults) > 0): ?>
            <div class="list-group-item" style="background-color: #e8faff;">
                <div class="row">
                    <div class="col-4">Title</div>
                    <div class="col-3">Description</div>
                    <div class="col-1" align="center">Category</div>
                    <div class="col-3" align="center">Posted By</div>
                    <div class="col-1" align="center"></div>
                </div>
            </div>
            <?php foreach($tresults as $r): ?>
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
                        <div class="col-1" align="center"><?php safer_echo($r["category"]) ?></div>
                        <div class="col-3" align="center"><a href="<?php echo get_url("profile.php?id=" . $r["user_id"]); ?>"><?php safer_echo($r["username"]) ?></a></div>
                        <div class="col-1" align="center">
                            <a href="<?php echo get_url("results.php?id=" . $r["id"]); ?>" class="btn btn-primary" role="button">Results</a>
                            <?php if(has_role("Admin")): ?>
                                <a href="<?php echo get_url("edit_survey.php?id=" . $r["id"]); ?>" class="btn btn-info" role="button" style="margin-top: 5px;">Edit</a>
                            <?php endif; ?>
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
                <?php safer_echo($profile_data["username"] . " hasn't taken any surveys yet!"); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if(isset($tpage) && isset($ttotal_pages)):?>
        <nav aria-label="Taken Surveys" style="margin: 40px;">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($tpage - 1) < 1?"disabled":"";?>">
                    <a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&tpage=<?php echo $tpage - 1;?>&ypage=<?php echo $ypage; ?>" tabindex="-1">Previous</a>
                </li>
                <?php for($i = 0; $i < $ttotal_pages; $i++):?>
                    <li class="page-item <?php echo ($tpage - 1) == $i?"active":"";?>"><a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&tpage=<?php echo ($i+1);?>&ypage=<?php echo $ypage; ?>"><?php echo ($i + 1);?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($tpage) >= $ttotal_pages?"disabled":"";?>">
                    <a class="page-link" href="<?php echo get_url("profile.php"); ?>?id=<?php echo $id; ?>&tpage=<?php echo $tpage + 1;?>&ypage=<?php echo $ypage; ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif;?>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
