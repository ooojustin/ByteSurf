<?php
    require('../inc/server.php');
    
    $errors = 0;
    
    session_start();
    if (!empty( $_POST)) {
        $_SESSION["POST"] = $_POST;
        if (!headers_sent()) {
            $location = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("location: " . $location);
            die();
        }
    }    
    
    if (isset($_SESSION["POST"])) {
        $_POST = $_SESSION["POST"];
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    if (empty($_POST['username'])) {
        $errors++;
        $error_message = "Please enter a username";
    }
    
    if (empty($_POST['email'])) {
        $errors++;
        $error_message = "Please enter an email";
    }
    
    if (empty($_POST['password'])) {
        $errors++;
        $error_message = "Please enter a password";
    }
    
    if (check_user_exists($_POST['username'])) {
        $errors++;
        $error_message = "Username already exists";
    }
    
    if (check_email_exists($_POST['email'])) {
        $errors++;
        $error_message = "Email already exists";
    }
    
    // we need to make a banner to display $error_message at some point..
    
    
    if ($errors < 1) {
        create_account($_POST['username'], $_POST['email'], $_POST['password']);
        header("location: ..\home");
        unset($_SESSION["POST"]);
        die();
    }
    
    unset($_SESSION["POST"]);
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
	<meta name="keywords" content="">A
	<meta name="author" content="Anthony Almond">
	<title>jexflix</title>

</head>
<body class="body">

	<div class="sign section--bg" data-bg="img/section/section.jpg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="sign__content">
						<!-- registration form -->
						<form action="" method="post" class="sign__form">
							<a href="../home" class="sign__logo">
								<img src="../img/logo.svg" alt="">
							</a>

							<div class="sign__group">
								<input type="text" class="sign__input" id="username" name="username" placeholder="Username">
							</div>

							<div class="sign__group">
								<input type="text" class="sign__input" id="email" name="email" placeholder="Email">
							</div>

							<div class="sign__group">
								<input type="password" class="sign__input" id="password" name="password" placeholder="Password">
							</div>

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