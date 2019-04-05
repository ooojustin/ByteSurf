<?php

    require('../inc/server.php');
    require('../inc/session.php');

	if (is_logged_in()) {
		header("location: https://bytesurf.io/home");
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
<html lang="en" class="no-js">
<head>
	<!-- Basic need -->
	<title>ByteSurf</title>
	<meta charset="UTF-8">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<link rel="profile" href="#">

    <!--Google Font-->
    <link rel="stylesheet" href='http://fonts.googleapis.com/css?family=Dosis:400,700,500|Nunito:300,400,600' />
	<!-- Mobile specific meta -->
	<meta name=viewport content="width=device-width, initial-scale=1">
	<meta name="format-detection" content="telephone-no">

	<!-- CSS files -->
	<link rel="stylesheet" href="../css/plugins.css">
	<link rel="stylesheet" href="../css/style.css">

</head>
<body style="background-color: #020d18;">
    
<div class="page-single">
	<div class="container" style="width: 500px; max-width: 100%">
			<div class="col-12">
				<div class="form-style-1 user-pro">

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

					<form action="" class="user" method="post">
						<center><h4 style="font-size: 20px; margin-bottom: 10px">Register</h4></center>
						<div class="row">
							<div class="col-md-12 form-it">
								<label>Username</label>
								<input type="text" placeholder="Username" name="username">
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 form-it">
								<label>Email</label>
								<input type="text" placeholder="Email" name="email">
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 form-it">
								<label>Password</label>
								<input type="password" placeholder="Password" name="password">
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 form-it">
								<label>Confirm Password</label>
								<input type="password" placeholder="Confirm Password" name="password_confirm">
							</div>
						</div>
						<? if (!isset($_GET['r'])) { ?>
						<div class="row">
							<div class="col-md-12 form-it">
								<label>Referrer</label>
								<input type="text" placeholder="Referrer" id="referrer" onchange="referrerChanged()">
							</div>
						</div>
						<? } ?>
						<div class="row">
							<div class="col-md-6" style="float: right">
								<input class="submit" type="submit" value="Sign Up">
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
    
</body>
</html>