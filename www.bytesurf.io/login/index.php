<?php

    require('../inc/server.php');
    require('../inc/session.php');

	if (is_logged_in()) {
		header("location: https://bytesurf.io/home/");
		die();
	}

    // if username and password arent set, they're not trying to login
    if (!isset($_POST['username']) || !isset($_POST['password']))
        goto skip_login;

    // try to login if username/password are provided and not empty
    if (empty($_POST['username']) || empty($_POST['password']))
    	$issue = 'Please enter username/password.';
    else if (login($_POST['username'], $_POST['password'])) {

    	// log login info into database
    	global $db, $ip;
    	$log_login = $db->prepare('INSERT INTO logins (username, ip_address, timestamp) VALUES (:username, :ip_address, :timestamp)');
    	$log_login->bindValue(':username', $_POST['username']);
    	$log_login->bindValue(':ip_address', $ip);
    	$log_login->bindValue(':timestamp', time());
    	$log_login->execute();

    	// create session, proceed to home page/referrer page
       	$_SESSION['id'] = get_user($_POST['username'])['id'];
        $location = '../home';
        
        // redirect to a stored page, if necessary
        if (isset($_GET['r']) && isset($_SESSION['login_redirect']))
            $location = $_SESSION['login_redirect'];
        
        unset($_SESSION['login_redirect']);
       	header("location: " . $location);
       	die();

    } else
        $issue = 'Incorrect username/password.';

    skip_login:

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

	<!-- Favicons -->
	<link rel="icon" type="image/png" href="../icon/favicon-32x32.png" sizes="32x32">
	<link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">

	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="Peter Pistachio">
	<title>ByteSurf</title>

</head>
<body class="body">

	<div class="sign section--bg" data-bg="../img/section/section.jpg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="sign__content">
						<!-- authorization form -->
						<form action="" method="POST" class="sign__form">

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
								<input type="password" class="sign__input" id="password" name="password" placeholder="Password">
							</div>

							<div class="sign__group sign__group--checkbox">
								<input id="remember" name="remember" type="checkbox" checked="checked">
								<label for="remember">Remember Me</label>
							</div>
							
							<button class="sign__btn" type="submit">Sign in</button>

							<span class="sign__text">Don't have an account? <a href="../register">Sign up!</a></span>

							<span class="sign__text"><a href="reset">Forgot password?</a></span>
							
						</form>
						<!-- end authorization form -->
					</div>
				</div>
			</div>
		</div>
	</div>

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
</body>
</html>