<!doctype html>
<html>
<head>
    <title>HUGE</title>
    <!-- META -->
    <meta charset="utf-8">
    <!-- send empty favicon fallback to prevent user's browser hitting the server for lots of favicon requests resulting in 404s -->
    <link rel="icon" href="data:;base64,=">
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo Config::get('HTTPS_URL'); ?>css/style.css" />
</head>
<body>
    <!-- wrapper, to center website -->
    <div class="navwrapper">
        <div class="navigation logo"> 
            <a href="/huge" title="index">LOGO</a>
        </div>
        <!-- navigation -->
        <ul class="navigation right">
            <li <?php if (View::checkForActiveController($filename, "index")) { echo ' class="active" '; } ?> >
                <a href="<?php echo Config::get('HTTPS_URL'); ?>">Home</a>
            </li>
            <?php if (Session::userIsLoggedIn()) { ?>
                <li <?php if (View::checkForActiveController($filename, "dashboard")) { echo ' class="active" '; } ?> >
                    <a href="<?php echo Config::get('HTTPS_URL'); ?>dashboard/index">Dashboard</a>
                </li>
                <li <?php if (View::checkForActiveController($filename, "note")) { echo ' class="active" '; } ?> >
                    <a href="<?php echo Config::get('HTTPS_URL'); ?>note/index">My Notes</a>
                </li>
            <?php } else { ?>
                <!-- for not logged in users -->
                <li <?php if (View::checkForActiveControllerAndAction($filename, "login/index")) { echo ' class="active" '; } ?> >
                    <a href="<?php echo Config::get('HTTPS_URL'); ?>login">Login</a>
                </li>
                <li <?php if (View::checkForActiveControllerAndAction($filename, "login/register")) { echo ' class="active" '; } ?> >
                    <a href="<?php echo Config::get('HTTPS_URL'); ?>login/register">Register</a>
                </li>
            <?php } ?>

        <!-- my account -->

            <?php if (Session::userIsLoggedIn()) : ?>
            <?php if (Session::get("user_account_type") == 7) : ?>
            <li <?php if (View::checkForActiveController($filename, "admin")) {echo ' class="active" ';} ?> >
                <a href="<?php echo Config::get('HTTPS_URL'); ?>admin/">Admin</a>
                <ul class="navigation-submenu">
                    <li <?php if (View::checkForActiveController($filename, "profile")) { echo ' class="active" '; } ?> >
                        <a href="<?php echo Config::get('HTTPS_URL'); ?>profile/index">Profile list</a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            <li <?php if (View::checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                <a href="<?php echo Config::get('HTTPS_URL'); ?>login/showprofile">My Account</a>
                <ul class="navigation-submenu">
                    <li <?php if (View::checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                        <a href="<?php echo Config::get('HTTPS_URL'); ?>login/changeUserRole">Change account type</a>
                    </li>
                    <li <?php if (View::checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                        <a href="<?php echo Config::get('HTTPS_URL'); ?>login/editAvatar">Edit your avatar</a>
                    </li>
                    <li <?php if (View::checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                        <a href="<?php echo Config::get('HTTPS_URL'); ?>login/editusername">Edit my username</a>
                    </li>
                    <li <?php if (View::checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                        <a href="<?php echo Config::get('HTTPS_URL'); ?>login/edituseremail">Edit my email</a>
                    </li>
                    <li <?php if (View::checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                        <a href="<?php echo Config::get('HTTPS_URL'); ?>login/changePassword">Change Password</a>
                    </li>
                    <li <?php if (View::checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                        <a href="<?php echo Config::get('HTTPS_URL'); ?>login/changeUserDetails">Change User Details</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="<?php echo Config::get('HTTPS_URL'); ?>login/logout">Logout</a>
            </li>
        <?php endif; ?>
        </ul>

    </div>
        <div class="wrapper">