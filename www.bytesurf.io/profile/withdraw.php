<?php

    require('../inc/server.php');
    require('../inc/session.php');
    require('../inc/coinpayments/cp.php');
    require_subscription();

    global $user;
    
    $username = $user['username'];
    $affiliate_balance = round($user['affiliate_balance'], 2, PHP_ROUND_HALF_DOWN);
    
    if ($affiliate_balance < 5)
        msg('You can\'t withdraw yet!', 'We required a minimum balance of $5 before you\'re able to withdraw your earnings. Keep inviting your friends and try again!');
    
    if (!isset($_POST['amount']))
        goto skip_withdraw;

    $amount = doubleval($_POST['amount']);
    $btc_address = $_POST['btc_address'];

    if ($amount < 5) {
        $issue = "Minimum withdrawal amount is $5 USD.";
    } else if ($amount > $affiliate_balance) {
        $issue = "Amount exceeds your balance: " . $affiliate_balance;
    } else if (!validate_btc_address($btc_address)) {
        $issue = "The BTC address you've provided is invalid.";
    } else {
        
        // actually try to withdraw the money!!!
        $note = 'Affiliate Withdrawal - ' . $username . ' - ' . $btc_address . ': $' . $amount;
        $success = create_btc_withdrawal($username, $btc_address, $amount, $note);
        
        // subtract the $ from their balance
        remove_affiliate_balance($username, $amount);
        
        // check if we did it right or not
        if ($success) {
            msg('You just got a little richer.', 'You\'re withdrawal has been completed successfully! After one of our staff members accepts the withdrawal, you\'ll be emailed with a link allowing you to track your money. The BTC should hit your wallet in < 24 hours. Thanks for working with ByteSurf!', 'THANKS :D', 'https://bytesurf.io/home');
        } else {
            msg('Something went wrong...', 'Unfortunately, we were unable to process your withdrawal at this time. Our team has been notified about the issue. Please try again later!', 'AWWW :(', 'https://bytesurf.io/home');
            $message = sprintf('<p>A ByteSurf user experienced an issue while trying to withdraw their affiliate balance.</p><p><strong>User:</strong>&nbsp;%s</p><p><strong>Amount:</strong> $%s</p><p><strong>BTC Address:</strong>&nbsp;%s</p>', $username, $amount, $btc_address);
            send_email('ByteSurf Affiliate - Withdrawal Error', $message, 'withdraw@bytesurf.io', 'ByteSurf Withdrawal', 'support@bytesurf.io', 'ByteSurf Staff');
        }
        
    }

    skip_withdraw:
    
?>
<!DOCTYPE html,
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
	<title>ByteSurf - Withdraw Earnings</title>

</head>
<body class="body">

	<div class="sign section--bg" data-bg="../img/section/section.jpg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="sign__content">
                        
						<!-- withdrawal form -->
						<form action="" method="POST" class="sign__form" style="width: 50%">

							<a href="#" style="margin-bottom: 20px" class="sign__logo">
								<img src="../img/logo.png" alt="">
							</a>

							<? if (isset($issue)) { ?>
							<div class="register-error" style="margin-bottom: 15px">
							    <span class="signin-error-text"><?= $issue ?></span>
							</div>
							<? } ?>
                            
                            <center style="margin-bottom: 15px;">
                                <span class="sign__text">
                                    How much would you like to withdraw?<br>
                                    <b>Maximum: </b>$<?= $affiliate_balance ?><br>
                                    <b>Minimum: </b>$5.00
                                </span>
                            </center>

							<div class="sign__group"  style="width: 100%">
								<input type="number" class="sign__input" name="amount" style="width: 100%; text-align: center;"
                                    value="<?= $affiliate_balance ?>"
                                    step="0.01"
                                    min="5"
                                    max="<?= $affiliate_balance ?>"
                                >
							</div>
                            
                            <div class="sign__group"  style="width: 100%">
								<input type="text" class="sign__input" name="btc_address" style="width: 100%; text-align: center;" placeholder="Bitcoin Address">
							</div>
							
							<button style="margin-top: 0px" class="sign__btn" type="submit">Withdraw</button>
							
						</form>
						<!-- end withdrawal form -->
                        
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>