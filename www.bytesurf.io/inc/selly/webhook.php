<?php
	
	require 'utils.php';
	require '../products.php';
  	global $products;

	$post_body = file_get_contents('php://input');
	$data = json_decode($post_body, true);

	if (!isset($_GET['invoice']))
		die('Needs invoice.');

	$invoice = get_order_pp($_GET['invoice']);
	$product = $products[$invoice['product_number']];
	$username = $invoice['username'];
    $amount_usd = $invoice['amount'];

	if ($data['status'] == 100) {

		// determine duration, update subscription
		$duration = $product['duration'];
		if ($duration == -1)
			update_expires($username, -1); // lifetime
		else
			add_subscription_time($username, $duration);

		// give buyer 1 week trial codes (if applicable)
		for ($i = 0; $i < $product['trial_keys']; $i++)
			generate_trial_key($username, SECONDS_PER_DAY * 7);

		// give referrer 10% of the sale price (if applicable)
		$referrer = get_user_registration($username)['referrer'];
		$first_order = count(get_orders($username)) == 0;
		if (!is_null($referrer) && get_user($referrer) && $first_order)
            add_affiliate_balance($referrer, $amount_usd * 0.10);

		// remove credit from reseller account, update last_purchase
		reseller_received_payment($invoice['reseller']);
		remove_reseller_balance($invoice['reseller'], $invoice['amount'] * 0.75);

		update_order_pp($_GET['invoice'], 'completed');
		die();

	}

	switch ($data['status']) {
		case 51:
			// paypal chargeback
			break;
		case 55:
			// paypal payment pending
			break;
		case 56:
			// payment refunded
			break;
	}

?>