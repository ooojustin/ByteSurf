<?php

	// Data received when payment is created successfully: https://pastebin.com/i4TzNPgt

	require dirname(__FILE__) . '/CoinpaymentsAPI.php';
	require dirname(__FILE__) . '/utils.php';

	// coinpayments api keys
	define('PRIVATE_KEY', 'dB05204b1023751C8EC41eBB04b0efE255359b849fA9578e21259Bc66eDe7668');
	define('PUBLIC_KEY', '34d1d824c6fcd47436863b34beaa7487d29a39f40af249eb115719c97607d6f2');

	// btc address to receive payments
	define('BTC_ADDRESS', '1AnSFnaAgvtrsXZm82ZCqYv7M5c8cLKv64');

	// ipn url (to handle payment status updates)
	define('IPN_URL', 'https://jexflix.com/inc/coinpayments/ipn.php');

	// initialize CoinpaymentsAPI object
	$GLOBALS['cp'] = new CoinpaymentsAPI(PRIVATE_KEY, PUBLIC_KEY, 'json');

	function create_btc_payment($amount, $email, $name, $product_name, $product_number, $custom_info = '') {

		global $cp;

		// create order in database and get invoice string
		$invoice = create_order($name, $amount, 'btc');

		// send payment request to server
		$payment = $cp->CreateComplexTransaction(
			$amount, // amount of $ (USD)
			'USD', // transaction to convert from 
			'BTC', // transaction to convert to/receive payment in
			$email, // buyer email address
			BTC_ADDRESS, // seller btc address
			$name, // buyer full name (or username, i guess)
			$product_name, // name of product
			$product_number, // number of product (pass as string)
			$invoice, // invoice # (generated randomly for each payment)
			$custom_info, // anything (custom information to store)
			IPN_URL // URL to IPN to update/handle payments
		);

		// handle potential issues
		if ($payment['error'] != 'ok') {
			update_order($invoice, 'error');
			return false;
		}

		// return status URL
		return $payment['result']['status_url'];

	}

?>