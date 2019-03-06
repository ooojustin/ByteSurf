<?php
	
	require 'utils.php';
	require '../products.php';
  	global $products;

	$post_body = file_get_contents('php://input');
	$data = json_decode($post_body, true);

	if (!isset($_GET['invoice']))
		die('Needs invoice.');

	$invoice = get_order($_GET['invoice']);
	$product = $products[$product_id];
	$username = $invoice['username'];

	if ($data['status'] == 100) {

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