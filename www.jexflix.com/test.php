<?php

	require 'inc/coinpayments/cp.php';

	$data = create_btc_payment(5, 'justin@garofolo.net', 'Justin Garofolo', 'Test Product', '1');
	var_dump($data);
	//die(generate_invoice_string());

?>