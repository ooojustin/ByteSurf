<?php

    require('../inc/server.php');
    require('../inc/session.php');
    require('../inc/google_authenticator.php');
    
    // make sure pending user to login is set
    if (!isset($_SESSION['id_pending'])) {
        header('location: index.php');
        die();
    }

    // get user information
    $user = get_user_by_id($_SESSION['id_pending']);

    // make sure 2fa is enabled
    if (is_null($user['2fa']))
        login_2fa();

    // check the code, log them in if we can
    if (isset($_GET['code'])) {
        $auth = new GoogleAuthenticator();
        $valid = $auth->verifyCode($user['2fa'], $_GET['code'], 2);
        if ($valid) {
            log_login($user['username']);
            login_2fa();
        } else
            $issue = 'You\'ve entered an invalid 2FA code.';
    }

    function login_2fa() {
        
        // authorize user login by putting id_pending into session id
        $_SESSION['id'] = $_SESSION['id_pending'];
        
        // default redirect path
        $location = '../home';    
        
        // redirect to a stored page, if necessary
        if (isset($_GET['r']) && isset($_SESSION['login_redirect']))
            $location = $_SESSION['login_redirect'];
        
        // unset session vars that we wont need anymore
        unset($_SESSION['id_pending']);
        unset($_SESSION['login_redirect']);
        
        header('location: ' . $location);
        die();
        
        
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
	<meta name="keywords" content="">
	<meta name="author" content="Peter Pistachio">
	<title>ByteSurf - 2FA</title>

</head>
<body class="body">

	<div class="sign section--bg" data-bg="../img/section/section.jpg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="sign__content">
						<!-- authorization form -->
						<form action="" method="GET" class="sign__form">

							<a href="#" class="sign__logo">
								<img src="../img/logo.png" alt="">
							</a>

							<? if (isset($issue)) { ?>
							<div class="register-error">
							    <span class="signin-error-text"><?= $issue ?></span>
							</div>
							<? } ?>
                            
                            <? if (isset($_GET['r'])) { ?>
                            <input type="hidden" name="r" value="<?= htmlspecialchars($_GET['r']); ?>">
                            <? } ?>

							<div class="sign__group">
								<input type="text" class="sign__input" id="code" name="code" placeholder="ABC123">
							</div>
							
							<button class="sign__btn" type="submit">Authenticate</button>
							
						</form>
						<!-- end authorization form -->
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>