<?php
	
	// DOCUMENTATION: https://www.coinpayments.net/merchant-tools-ipn
	// Some IPN vars: https://pastebin.com/6YWVBaQV

	define('MERCHANT_ID', 'e9e52a8f8fc84397ff3a71c06301d352');
	define('SECRET', 'tkpVkHD3l0eAedf');

	if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC']))
  		die("No HMAC signature sent");

	$merchant = isset($_POST['merchant']) ? $_POST['merchant'] : '';
	if (empty($merchant))
  		die("No Merchant ID passed");

	if ($merchant != MERCHANT_ID)
  		die("Invalid Merchant ID");

	$request = file_get_contents('php://input');
	if ($request === FALSE || empty($request))
  		die("Error reading POST data");

	$hmac = hash_hmac("sha512", $request, SECRET);
	if ($hmac != $_SERVER['HTTP_HMAC'])
  		die("HMAC signature does not match");

  	require 'utils.php'; // includes main server and utils stuff
  	require '../products.php';
  	global $products;

	if ($_POST['status'] == 2 || $_POST['status'] >= 100) {

		// get invoice user & data
		$invoice = get_order($_POST['invoice']);
		$username = $invoice['username'];
		$user = get_user($username);
		$expires = intval($user['expires']);

		// get invoice product
		$product_id = intval($_POST['item_number']);
		$product = $products[$product_id];

		// apply subscription
		$duration = $product['duration'];
		if ($duration == -1)
			update_expires($username, -1); // lifetime
		else
			add_subscription_time($username, $duration);

		// give buyer 1 week trial codes (if applicable)
		for ($i = 0; $i < $product['trial_keys']; $i++)
			generate_trial_key($username, SECONDS_PER_DAY * 7);

		update_order($_POST['invoice'], 'completed');
		die();

	}

	switch ($_POST['status']) {
		case -2: // paypal refund/reversal
			update_order($_POST['invoice'], 'refunded');
			break;
		case -1: // cancelled / timed out
			update_order($_POST['invoice'], 'cancelled');
			break;
		case 0: // waiting for funds
			break;
		case 1: // coin reception confirmed
			update_order($_POST['invoice'], 'confirming');
			break;
		case 3:
			break; // paypal transaction pending (eChecks and stuff)
	}

?>