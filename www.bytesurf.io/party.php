<?php

    require 'inc/server.php';
    require 'inc/session.php';

    global $db, $user;
    require_subscription();

    // make sure action is set and valid
    if (!isset($_GET['action']))
        msg('Failed', 'Something went wrong :(');

    switch ($_GET['action']) {
            
        case 'create':
            $party_id = create_party();
            die('Party created: ' . $party_id);
            break;
            
        case 'leave';
            unset($_SESSION['party']);
            die('Left party.');
            break;
            
        case 'join':
            
            // make sure we have party set in url
            if (!isset($_GET['p']))
                msg('Failed', 'Party ID not specified :(');
            
            // make sure the party is valid
            $party = get_party($_GET['p']);
            if (!$party)
                msg('Failed', 'Specified party ID was invalid :(');
                
            // join the party
            $_SESSION['party'] = $_GET['p'];
            
            // redirect to current link
            // ...
            
            die('Party joined.');
            
            break;
            
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

							<a href="#" style="margin-bottom: 20px" class="sign__logo">
								<img src="../img/logo_party.png" alt="">
							</a>

							<? if (isset($issue)) { ?>
							<div class="register-error" style="margin-bottom: 15px">
							    <span class="signin-error-text"><?= $issue ?></span>
							</div>
							<? } ?>
                            
                            <center style="max-width: 300px; margin-bottom: 15px;"><span class="sign__text">Please enter the 6 digit code from your authenticator app.</span></center>
                            

							<div class="sign__group">
								<input type="text" class="sign__input" id="code" name="code" placeholder="ABC123" maxlength="6">
							</div>
							
							<button style="margin-top: 0px" class="sign__btn" type="submit">Authenticate</button>
                            
						</form>
						<!-- end authorization form -->
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>