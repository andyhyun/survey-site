<!--
Old CSS that might be used again later
<link rel="stylesheet" href = "static/css/styles.css">
-->
<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>

<!-- CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
      integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

<!-- jQuery and JS bundle w/ Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
        crossorigin="anonymous"></script>

<div class="container-fluid" style="padding: 0;">
    <nav class="navbar navbar-expand-lg navbar-light bg-dark">
        <ul class = "navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link text-light" href="<?php echo get_url("home.php"); ?>">Home</a></li>
            <?php if (!is_logged_in()): ?>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo get_url("login.php"); ?>">Login</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo get_url("register.php"); ?>">Register</a></li>
            <?php endif; ?>
            <?php if(has_role("Admin")): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Admin
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="nav-link" href="<?php echo get_url("test/test_create_survey.php"); ?>">Create Survey</a>
                        <a class="nav-link" href="<?php echo get_url("test/test_list_survey.php"); ?>">View Surveys</a>
                        <a class="nav-link" href="<?php echo get_url("test/test_create_question.php"); ?>">Create Question</a>
                        <a class="nav-link" href="<?php echo get_url("test/test_list_questions.php"); ?>">View Questions</a>
                    </div>
                </li>
            <?php endif; ?>
            <?php if (is_logged_in()): ?>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo get_url("create_survey.php"); ?>">Create Survey</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo get_url("your_surveys.php"); ?>">Your Surveys</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo get_url("public_surveys.php"); ?>">Public Surveys</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo get_url("profile.php"); ?>">Profile</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo get_url("logout.php"); ?>">Logout</a></li>
            <?php endif; ?>
        </ul>
    <nav>
</div>