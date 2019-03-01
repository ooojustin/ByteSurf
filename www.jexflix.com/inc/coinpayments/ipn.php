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

  	require 'utils.php';
  	require '../products.php';

	if ($_POST['status'] == 2 || $_POST['status'] >= 100) {
		$invoice = get_order($_POST['invoice']);
		$username = $invoice['username'];
		
		update_order($_POST['invoice'], 'completed');
		die();
	}

	switch ($_POST['status']) {
		case -2: // paypal refund/reversal
			break;
		case -1: // cancelled / timed out
			break;
		case 0: // waiting for funds
			break;
		case 1: // coin reception confirmed
			break;
		case 3:
			break; // paypal transaction pending (eChecks and stuff)
	}

?>