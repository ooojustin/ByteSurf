<?php

    require('../../inc/server.php');
    require('../../inc/session.php');
    require('utils.php');
    global $db;

	if (is_logged_in()) {
		header("location: https://bytesurf.io/home/");
		die();
	}

	if (isset($_POST['email'])) {

		if (empty($_POST['email']))
			$issue = 'Please provide an email address.';
		else if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === FALSE)
			$issue = 'Provided email address is invalid.';
		else {

			// get user from email address
			$user = get_user_by_email($_POST['email']);

			if ($user) {

				// generate unique code
				$code = generate_split_string(3, 3);

				// add row to db
				$create = $db->prepare('INSERT INTO password_resets (code, email, timestamp) VALUES (:code, :email, :timestamp)');
				$create->bindValue(':code', $code);
				$create->bindValue(':email', $user['email']);
				$create->bindValue(':timestamp', time());
				$create->execute();

				// generate the email message
				define('RESET_URL', 'https://bytesurf.io/login/reset/?code=');
				$message = file_get_contents('email_template.txt');
				$message = str_replace('{username}', $user['username'], $message);
				$message = str_replace('{url}', RESET_URL . $code, $message);

				// send email
				$subject = 'ByteSurf - Password Reset';
				log_email($user['email'], $subject, 'Password Reset');
				send_email(
					'ByteSurf - Password Reset', // subject
					$message, // message 
					'reset@ByteSurf.com', // from email
					'ByteSurf', // from name
					$user['email'], // to email
					$user['username'] // to name
				);

				die('Password reset submitted successfully.');

			} else
				$issue = 'No user found with that email address.';

		}

	} else if (isset($_GET['code']) && !isset($_POST['password'])) {

		$password_reset = get_password_reset($_GET['code']);
		if (is_password_reset_expired($password_reset)) {
			update_password_reset_status($_GET['code'], 'expired');
			$issue = 'Provided password reset is already expired.';
		} else if ($password_reset['status'] != 'pending')
			$issue = 'Provided password reset is no longer pending. (' . $password_reset['status'] . ')';

	} else if (isset($_GET['code']) && isset($_POST['password'])) {

		$password_reset = get_password_reset($_GET['code']);
		if (!isset($_POST['confirm_password']) || empty($_POST['confirm_password']) || $_POST['confirm_password'] != $_POST['password'])
			$issue = 'Provided passwords do not match.';
		else {
			$username = get_user_by_email($password_reset['email'])['username'];
			update_password_nocheck($username, $_POST['password']);
			update_password_reset_status($_GET['code'], 'completed');
			die('Password reset completed successfully.');
		}

	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Font -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600%7CUbuntu:300,400,500,700" rel="stylesheet">

	<!-- CSS -->
	<link rel="stylesheet" href="../../css/bootstrap-reboot.min.css">
	<link rel="stylesheet" href="../../css/bootstrap-grid.min.css">
	<link rel="stylesheet" href="../../css/owl.carousel.min.css">
	<link rel="stylesheet" href="../../css/jquery.mCustomScrollbar.min.css">
	<link rel="stylesheet" href="../../css/nouislider.min.css">
	<link rel="stylesheet" href="../../css/ionicons.min.css">
	<link rel="stylesheet" href="../../css/plyr.css">
	<link rel="stylesheet" href="../../css/photoswipe.css">
	<link rel="stylesheet" href="../../css/default-skin.css">
	<link rel="stylesheet" href="../../css/main.css">

	<!-- Favicons -->
	<link rel="icon" type="image/png" href="../../icon/favicon-32x32.png" sizes="32x32">
	<link rel="apple-touch-icon" sizes="180x180" href="../../apple-touch-icon.png">

	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="Anthony Almond">
	<title>ByteSurf</title>

</head>
<body class="body">

	<div class="sign section--bg" data-bg="../../img/section/section.jpg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="sign__content">
						<!-- authorization form -->
						<form action="" method="post" class="sign__form">

							<a href="#" class="sign__logo">
								<img src="../../img/logo.png" alt="">
							</a>

							<? if (isset($issue)) { ?>
							<div class="register-error">
							    <span class="signin-error-text"><?= $issue ?></span>
							</div>
							<? } ?>

							<? if (isset($_GET['code']) && !isset($_POST['password'])) { ?>
							<!-- NEW PASSWORD SUBMISSION-->

							<div class="sign__group">
								<input type="password" class="sign__input" id="password" name="password" placeholder="Password">
							</div>

							<div class="sign__group">
								<input type="password" class="sign__input" id="confirm_password" name="confirm_password" placeholder="Confirm Password">
							</div>

							<!-- END NEW PASSWORD SUBMISSION-->
							<? } else { ?>
							<!-- EMAIL SUBMISSION-->

							<label class="profile__label" for="email">Enter your email address to continue.</label>

							<div class="sign__group">
								<input type="text" class="sign__input" id="email" name="email" placeholder="Email Address">
							</div>

							<!-- END EMAIL SUBMISSION-->
							<? } ?>
							
							<button class="sign__btn" type="submit">Reset Password</button>
							
						</form>
						<!-- end authorization form -->
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- JS -->
	<script src="../../js/jquery-3.3.1.min.js"></script>
	<script src="../../js/bootstrap.bundle.min.js"></script>
	<script src="../../js/owl.carousel.min.js"></script>
	<script src="../../js/jquery.mousewheel.min.js"></script>
	<script src="../../../js/jquery.mCustomScrollbar.min.js"></script>
	<script src="../../js/wNumb.js"></script>
	<script src="../../js/nouislider.min.js"></script>
	<script src="../../js/plyr.min.js"></script>
	<script src="../../js/jquery.morelines.min.js"></script>
	<script src="../../js/photoswipe.min.js"></script>
	<script src="../../js/photoswipe-ui-default.min.js"></script>
	<script src="../../js/main.js"></script>
</body>
</html>