<?php

	require dirname(__FILE__) . '/CoinpaymentsAPI.php';
	require dirname(__FILE__) . '/utils.php';

	// coinpayments api keys
	define('PRIVATE_KEY', 'dB05204b1023751C8EC41eBB04b0efE255359b849fA9578e21259Bc66eDe7668');
	define('PUBLIC_KEY', '34d1d824c6fcd47436863b34beaa7487d29a39f40af249eb115719c97607d6f2');

	// btc address to receive payments
	define('BTC_ADDRESS', '39pPscFe1e6b9YDtiwLe8E3FzG9UPTz2kg');

	// ipn url (to handle payment status updates)
	define('IPN_URL', 'https://bytesurf.io/inc/coinpayments/ipn.php');

	// initialize CoinpaymentsAPI object
	$GLOBALS['cp'] = new CoinpaymentsAPI(PRIVATE_KEY, PUBLIC_KEY, 'json');

	function create_btc_payment($username, $email, $name, $amount, $product_name, $product_number, $custom_info = '') {

		global $cp;

		// create order in database and get invoice string
		$invoice = create_order_btc($name, $email, $username, $product_name, $amount);

		// send payment request to server
		$payment = $cp->CreateComplexTransaction(
			$amount, // amount of $ (USD)
			'USD', // transaction to convert from 
			'BTC', // transaction to convert to/receive payment in
			$email, // buyer email address
			BTC_ADDRESS, // seller btc address
			$name, // buyer full name
			$product_name, // name of product
			$product_number, // number of product (pass as string)
			$invoice, // invoice # (generated randomly for each payment)
			$custom_info, // anything (custom information to store)
			IPN_URL // URL to IPN to update/handle payments
		);

		// handle potential issues
		if ($payment['error'] != 'ok') {
			update_order_btc($invoice, 'error');
			return false;
		}

		// set the transaction amount (btc)
		set_amount_btc($invoice, $payment['result']['amount']);

		// return status URL
		return $payment['result']['status_url'];

	}

    // https://www.coinpayments.net/apidoc-create-withdrawal
    function create_btc_withdrawal($username, $btc_address, $amount_USD, $note = '') {
        
        global $db, $cp;
        
        // establish variables to be sent to server
        $data['amount'] = $amount_USD; // amount in USD
        $data['currency'] = 'BTC';
        $data['currency2'] = 'USD';
        $data['address'] = $btc_address;
        $data['ipn_url'] = IPN_URL;
        $data['auto_confirm'] = 0; // it'll send us an email asking us to verify the amount, just in case!!!
        $data['note'] = $note; // whatever we want
        
        // send the request, get response as response
        $withdrawal = $cp->CreateWithdrawal($data);
        
        // make sure there wasn't an error
		if ($withdrawal['error'] != 'ok')
            return false;
        
        // insert withdrawal into database & return
        $log_withdrawal = $db->prepare('INSERT INTO affiliate_withdrawals (cp_id, username, btc_address, amount, timestamp) VALUES (?, ?, ?, ?, ?)');
        $log_withdrawal->bindValue(1, $withdrawal['result']['id']);
        $log_withdrawal->bindValue(2, $username);
        $log_withdrawal->bindValue(3, $btc_address);
        $log_withdrawal->bindValue(4, $amount_USD);
        $log_withdrawal->bindValue(5, time());
        return $log_withdrawal->execute();
        
    }

?>