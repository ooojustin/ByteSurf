<?php

    require('../inc/server.php');
    require('../inc/session.php');

	if (is_logged_in()) {
		header("location: https://jexflix.com/home/");
		die();
	}

	if (!isset($_POST['username']))
		goto skip_create_account;

	// a list of blacklisted usernames, cuz y not
	$blacklisted = array('mailer', 'admin', 'penguware', 'weebware');

    if (!isset($_POST['username']) || empty($_POST['username']))
    	$issue = 'Please enter a valid username.';
    else if (in_array($_POST['username'], $blacklisted))
    	$issue = 'The username you\'ve provided is blacklisted.';
    else if (!isset($_POST['email']) || empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    	$issue = 'Please enter a valid email address.';
    else if (!isset($_POST['password']) || empty($_POST['password']))
    	$issue = 'Please enter a password.';
    else if (get_user($_POST['username']))
        $issue = 'That username is not available.';  
    else if (get_user_by_email($_POST['email']))
    	$issue = 'Account already exists with that email address.';
    else if ($_POST['password'] != $_POST['password_confirm'])
        $issue = 'Passwords do not match';
    else {

    	// establish referrer
    	$referrer = NULL;
    	if (isset($_GET['r']) && get_user($_GET['r']))
    		$referrer = $_GET['r'];

    	// create account
        create_account($_POST['username'], $_POST['email'], $_POST['password'], $referrer);

        // redirect
        header("location: ../home");
        die();

    }

    skip_create_account:

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Font -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600%7CUbuntu:300,400,500,700" rel="stylesheet">

	<!-- CSS -->
	<link rel="stylesheet" href="../css/bootstrap-reboot.min.css">
	<link rel="stylesheet" href="../css/bootstrap-grid.min.css">
	<link rel="stylesheet" href="../css/owl.carousel.min.css">
	<link rel="stylesheet" href="../css/jquery.mCustomScrollbar.min.css">
	<link rel="stylesheet" href="../css/nouislider.min.css">
	<link rel="stylesheet" href="../css/ionicons.min.css">
	<link rel="stylesheet" href="../css/plyr.css">
	<link rel="stylesheet" href="../css/photoswipe.css">
	<link rel="stylesheet" href="../css/default-skin.css">
	<link rel="stylesheet" href="../css/main.css">

	<!-- JS -->
	<script src="../js/jquery-3.3.1.min.js"></script>
	<script src="../js/bootstrap.bundle.min.js"></script>
	<script src="../js/owl.carousel.min.js"></script>
	<script src="../js/jquery.mousewheel.min.js"></script>
	<script src="../js/jquery.mCustomScrollbar.min.js"></script>
	<script src="../js/wNumb.js"></script>
	<script src="../js/nouislider.min.js"></script>
	<script src="../js/plyr.min.js"></script>
	<script src="../js/jquery.morelines.min.js"></script>
	<script src="../js/photoswipe.min.js"></script>
	<script src="../js/photoswipe-ui-default.min.js"></script>
	<script src="../js/main.js"></script>

	<!-- Favicons -->
	<link rel="icon" type="image/png" href="../icon/favicon-32x32.png" sizes="32x32">
	<link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">

	<meta name="description" content="">
	<meta name="keywords" content="">A
	<meta name="author" content="Anthony Almond">
	<title>jexflix</title>

</head>
<body class="body">

	<div class="sign section--bg" data-bg="../img/section/section.jpg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="sign__content">

						<? if (!isset($_GET['r'])) { ?>
						<script>
							function referrerChanged() {
								var referrer = $('#referrer').val();
								if (referrer.length > 0)
									referrer = 'index.php?r=' + referrer;
								$('#register-form').attr('action', referrer);
							}
						</script>
						<? } ?>

						<!-- registration form -->
						<form id="register-form" action="" method="post" class="sign__form">
							
							<a href="#" class="sign__logo">
								<img src="../img/logo.png" alt="">
							</a>

							<? if (isset($issue)) { ?>
							<div class="register-error">
							    <span class="signin-error-text"><?= $issue ?></span>
							</div>
							<? } ?>

							<div class="sign__group">
								<input type="text" class="sign__input" id="username" name="username" placeholder="Username">
							</div>

							<div class="sign__group">
								<input type="text" class="sign__input" id="email" name="email" placeholder="Email">
							</div>

							<div class="sign__group">
								<input type="password" class="sign__input" id="password" name="password" placeholder="Password">
							</div>
							
							<div class="sign__group">
								<input type="password" class="sign__input" id="password_confirm" name="password_confirm" placeholder="Confirm Password">
							</div>

							<? if (!isset($_GET['r'])) { ?>
							<div class="sign__group">
								<input type="text" class="sign__input" id="referrer" placeholder="Referrer" onchange="referrerChanged()">
							</div>
							<? } ?>

							<div class="sign__group sign__group--checkbox">
								<input id="remember" name="remember" type="checkbox" checked="checked">
								<label for="remember">I agree to the <a href="#">Privacy Policy</a></label>
							</div>
							
							<button class="sign__btn" type="submit">Sign up</button>

							<span class="sign__text">Already have an account? <a href="../login">Sign in!</a></span>
						</form>
						<!-- registration form -->
					</div>
				</div>
			</div>
		</div>
	</div>

</body>
</html>