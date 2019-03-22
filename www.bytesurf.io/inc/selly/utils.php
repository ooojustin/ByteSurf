<?php
	
	if (!isset($GLOBALS['db']))
        require dirname(__FILE__) . '/../server.php';

    function update_order_pp($invoice, $status) {

        global $db;

        $update_order = $db->prepare('UPDATE orders_pp SET status = :status WHERE invoice = :invoice');
        $update_order->bindValue(':invoice', $invoice);
        $update_order->bindValue(':status', $status);

        return $update_order->execute();

    }
    	
    function create_order_pp($email, $username, $product_name, $product_number, $reseller, $amount) {

        global $db;

        // note: 'R-' before generated invoice indicates reseller payment
        $invoice = 'R-' . generate_split_string(5, 4);

        $create_order = $db->prepare('INSERT INTO orders_pp (invoice, email, username, product, product_number, reseller, amount, timestamp) VALUES (:invoice, :email, :username, :product, :product_number, :reseller, :amount, :timestamp)');
        $create_order->bindValue(':invoice', $invoice);
        $create_order->bindValue(':email', $email);
        $create_order->bindValue(':username', $username);
        $create_order->bindValue(':product', $product_name);
        $create_order->bindValue(':product_number', $product_number);
        $create_order->bindValue(':reseller', $reseller['username']);
        $create_order->bindValue(':amount', $amount);
        $create_order->bindValue(':timestamp', time());

        if ($create_order->execute())
            return $invoice;
        else
            return false;

    }

    function get_order_pp($invoice) {
        
        global $db;

        $get_order = $db->prepare('SELECT * FROM orders_pp WHERE invoice = :invoice LIMIT 1');
        $get_order->bindValue(':invoice', $invoice);
        $get_order->execute();

        return $get_order->fetch();

    }

?>