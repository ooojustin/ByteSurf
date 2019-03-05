<?php
	
	require '../inc/server.php';
    require '../inc/session.php';
    require '../inc/products.php';
    require_login();

    // get user/product/discount info
    global $user, $product_ids, $products, $discounts;

	// make sure a valid plan has been found
	if (!isset($_GET['plan']) || !array_key_exists($_GET['plan'], $product_ids)) {
		header("location: https://jexflix.com/pricing/");
        die();
	}

	// determine the current product
	// $product has keys: name, price, duration
	$id = $product_ids[$_GET['plan']];
	$product = $products[$id];

	// if we're depositing money, check the amount and whatnot
	$is_deposit = $_GET['plan'] == 'reseller';
	if ($is_deposit) {
		if (!isset($_GET['amount']) || !is_numeric($_GET['amount']))
			die('Invalid \'amount\' variable.');
		$product['price'] = floatval($_GET['amount']);
	} else if (!isset($_POST['method'])) {
		// if we're not depositing money, required 'method' var (payment method)
		// $_POST['method'] must be either 'paypal' or 'bitcoin' 
		header("location: https://jexflix.com/pricing/");
        die();
	}

	if (isset($_POST['name']) && isset($_POST['email'])) {
		
		$price = $product['price'];

		// handle discount code if provided
		if (isset($_POST['discount']) && !empty($_POST['discount']) && !$is_deposit)
			if (array_key_exists($_POST['discount'], $discounts))
				$price *= (100 - $discounts[$_POST['discount']]) / 100;

		// handle potential issues
		if (empty($_POST['name']))
			$issue = 'Please provide your name.';
		else if (empty($_POST['email']))
			$issue = 'Please provide your email address.';
		else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
			$issue = 'The provided email address is invalid.';

		if (!isset($issue)) {

			// there is no issue, create payment based on method
			switch ($_POST['method']) {

				case 'bitcoin':
				    require '../inc/coinpayments/cp.php';
					$url = create_btc_payment($user['username'], $_POST['email'], $_POST['name'], $price, $product['name'], strval($id));
					header("location: " . $url);
					die();

				case 'paypal':

					// get reseller
					$reseller = get_next_reseller($price);
					if (!$reseller) {
						$issue = 'PayPal is not currently available. Please try again later.';
						break;
					}

					// create paypal payment & redirect
					$url = create_paypal_payment($reseller, $product['name'], 'PayPal', $_POST['email'], $price);
					header("location: " . $url);
					die();

			}

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

</head>
<body class="body">

	<div class="sign section--bg" data-bg="../img/section/section.jpg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="sign__content">
						<!-- authorization form -->
						<form action="" method="post" class="sign__form">

							<a href="#" class="sign__logo" style="margin-bottom: 15px">
								<img src="../img/logo.png" alt="">
							</a>

                            <label class="profile__label"><?= $product['name'] ?></label>
                            <label class="profile__label" id="price_label">Price: <?= '$' . $product['price'] ?></label>

							<? if (isset($issue)) { ?>
							<div class="register-error">
							    <span class="signin-error-text"><?= $issue ?></span>
							</div>
							<? } ?>

							<div class="sign__group">
								<input type="text" class="sign__input" id="name" name="name" placeholder="Name">
							</div>

							<div class="sign__group">
								<input type="text" class="sign__input" id="email" name="email" placeholder="Email">
							</div>
							
							<? if (!$is_deposit) { ?>
							<div class="sign__group">
								<input type="text" class="sign__input" id="discount" name="discount" placeholder="Discount Code">
							</div>
							<? } ?>
							
							<button class="sign__btn" type="submit">Submit</button>
						</form>
						<!-- end authorization form -->
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>

		var price = <?= $product['price'] ?>;

		$('#discount').focusout(function() {
			var code = $('#discount').val();
			var url = 'https://jexflix.com/inc/products.php?discount=' + code;
			get(url, function(off) {
				if ($.isNumeric(off) && off > 0 && off < 100) {
					var multiplier = (100 - off) / 100;
            		var discounted_price = (price * multiplier).toFixed(2);
            		$('#price_label').text('Price: $' + discounted_price + ' (' + off + '% off)');
				} else {
					$('#price_label').text('Price: $' + price);
				}
			});
		});

		function get(url, callback) {
			$.ajax({
				'url': url,
				'type': 'GET',
				'success': callback
			});
		}

	</script>

</body>
</html>