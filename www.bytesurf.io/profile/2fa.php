<?php

    require '../inc/server.php';
    require '../inc/session.php';
    require('../inc/google_authenticator.php');
    require_login();

    // make sure action is set and valid
    if (!isset($_POST['action']) || ($_POST['action'] != 'enable' && $_POST['action'] != 'disable'))
        msg('Failed', 'Something went wrong :(');

    global $user;
    $auth = new GoogleAuthenticator();

    $enable = $_POST['action'] == 'enable';
    $secret = $enable ? (isset($_POST['secret']) ? $_POST['secret'] : $auth->createSecret()) : $user['2fa'];

    if (isset($_POST['code'])) {
        if ($auth->verifyCode($secret, $_POST['code'], 2)) {
            if ($enable) {
                update_2fa($secret);
                // msg('2FA Enabled', 'Two Factor Authentication has been enabled successfully. Congrats on you\'re more secure account!');
                header("location: index.php");
            } else {
                update_2fa(NULL);
                // msg('2FA Disabled', 'Two Factor Authentication has been disabled successfully.');
                header("location: index.php");
            }
        } else
            $issue = 'You\'ve entered an invalid 2FA code.';
    }

    function update_2fa($secret) {
        global $db, $user;
        $update_2fa = $db->prepare('UPDATE users SET 2fa=:2fa WHERE id=:id');
        $update_2fa->bindValue(':id', $user['id']);
        $update_2fa->bindValue(':2fa', $secret);
        return $update_2fa->execute();
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
						<form action="" method="POST" class="sign__form" style="padding-bottom: 30px">
                            
                            <? if ($enable) { ?>
                            <? $auth_img = $auth->getQRCodeGoogleUrl('ByteSurf', $secret, 'ByteSurf'); ?>
                            <!-- show the qr code, if they're enabling 2fa -->
                            <span class="sign__text" style="margin-bottom: 15px;"><a href="<?= $auth_img ?>"><b>Secret: <?= $secret ?></b></a></span>
							<a href="<?= $auth_img ?>" style="margin-bottom: 20px;">
								<img src="<?= $auth_img ?>" alt="">
							</a>
                            <center style="max-width: 300px; margin-bottom: 15px;"><span class="sign__text">Scan the code above in your authenticator app and enter the generated 6 digit code.</span></center>
                            <input type="hidden" name="secret" value="<?= $secret ?>">
                            <? } else { ?>
                            <center style="max-width: 300px; margin-bottom: 15px;"><span class="sign__text">Please enter the 6 digit code from your authenticator app to confirm.</span></center>
                            <? } ?>

                            <!-- display issue, if there's an error -->
							<? if (isset($issue)) { ?>
							<div class="register-error" style="margin-bottom: 15px;">
							    <span class="signin-error-text"><?= $issue ?></span>
							</div>
							<? } ?>
                            
                            <!-- pass action to next page -->
                            <input type="hidden" name="action" value="<?= htmlspecialchars($_POST['action']); ?>">

							<div class="sign__group">
								<input type="text" class="sign__input" id="code" name="code" placeholder="ABC123" maxlength="6">
							</div>
							
							<button class="sign__btn" type="submit" style="margin-top: 0px;"><?= ucfirst($_POST['action']) ?></button>
                            
							<span class="sign__text"><a href="index.php">Return to Profile</a></span>
                            
						</form>
						<!-- end authorization form -->
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>