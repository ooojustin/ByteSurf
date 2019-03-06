<?php

    if (!isset($GLOBALS['db']))
        require dirname(__FILE__) . '/../server.php';

    function update_order_btc($invoice, $status) {

        global $db;

        $update_order = $db->prepare('UPDATE orders_btc SET status = :status WHERE invoice = :invoice');
        $update_order->bindValue(':invoice', $invoice);
        $update_order->bindValue(':status', $status);

        return $update_order->execute();

    }

    function set_amount_btc($invoice, $amount_btc) {

        global $db;

        $set_amount = $db->prepare('UPDATE orders_btc SET amount_btc = :amount_btc WHERE invoice = :invoice');
        $set_amount->bindValue(':invoice', $invoice);
        $set_amount->bindValue(':amount_btc', $amount_btc);
        
        return $set_amount->execute();
        
    }
    
    function create_order_btc($name, $email, $username, $product, $amount_usd) {

        global $db;

        $invoice = generate_split_string(5, 4);

        $create_order = $db->prepare('INSERT INTO orders_btc (invoice, name, email, username, product, amount_usd) VALUES (:invoice, :name, :email, :username, :product, :amount_usd)');
        $create_order->bindValue(':invoice', $invoice);
        $create_order->bindValue(':name', $name);
        $create_order->bindValue(':email', $email);
        $create_order->bindValue(':username', $username);
        $create_order->bindValue(':product', $product);
        $create_order->bindValue(':amount_usd', $amount_usd);

        if ($create_order->execute())
            return $invoice;
        else
            return false;

    }

    function get_order_btc($invoice) {
        
        global $db;

        $get_order = $db->prepare('SELECT * FROM orders_btc WHERE invoice = :invoice LIMIT 1');
        $get_order->bindValue(':invoice', $invoice);
        $get_order->execute();

        return $get_order->fetch();

    }

?>