<?php

    require('../inc/server.php');
    require('../inc/session.php');

	$started = @session_start();

	if(!$started) {
		session_regenerate_id(true); // replace the Session ID
		session_start(); 
	}

	if (is_logged_in()) {
		header("location: https://bytesurf.io/home/");
		die();
	}

	// if $_POST['username'] isn't set, they didnt post data from form
	if (!isset($_POST['username']))
		goto skip_login;

    if (empty($_POST['username']) || empty($_POST['password'])) {
    	$issue = 'Please enter username/password.';
    } else {
    	if (login($_POST['username'], $_POST['password'])) {

    		// log login info into database
    		global $db, $ip;
    		$log_login = $db->prepare('INSERT INTO logins (username, ip_address, timestamp) VALUES (:username, :ip_address, :timestamp)');
    		$log_login->bindValue(':username', $_POST['username']);
    		$log_login->bindValue(':ip_address', $ip);
    		$log_login->bindValue(':timestamp', time());
    		$log_login->execute();

    		// create session, proceed to home page
			$_SESSION['id'] = get_user($_POST['username'])['id'];
			   			
			if (isset($_SESSION['last_page']))
				header("location: " . $_SESSION['last_page']);
			else
				header("location: ../home");

       		die();

    	} else
    		$issue = 'Incorrect username/password.';
    }

    skip_login:

    $redirect = '';
    if (isset($_GET['r']))
    	$redirect = '../' . $_GET['r'];

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
					<form action="<?= $redirect ?>" class="user" method="post">
						<center><h4 style="font-size: 20px; margin-bottom: 10px">Login</h4></center>
						<div class="row">
							<div class="col-md-12 form-it">
								<label>Username</label>
								<input type="text" placeholder="Username" name="username">
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 form-it">
								<label>Password</label>
								<input type="password" placeholder="Password" name="password">
							</div>
						</div>
						<div class="row">
							<div class="col-md-6" style="float: right">
								<input class="submit" type="submit" value="Sign In">
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