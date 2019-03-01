<?php

    if (!isset($GLOBALS['db']))
        require dirname(__FILE__) . '/../server.php';

    function update_order($invoice, $status) {

        global $db;

        $update_order = $db->prepare('UPDATE orders SET status = :status WHERE invoice = :invoice');
        $update_order->bindValue(':invoice', $invoice);
        $update_order->bindValue(':status', $status);

        return $update_order->execute();

    }
    
    function create_order($name, $email, $username, $product, $amount, $method) {

        global $db;

        $invoice = generate_split_string(5, 4);

        $create_order = $db->prepare('INSERT INTO orders (invoice, name, email, username, product, amount, method) VALUES (:invoice, :name, :email, :username, :product, :amount, :method)');
        $create_order->bindValue(':invoice', $invoice);
        $create_order->bindValue(':name', $name);
        $create_order->bindValue(':email', $email);
        $create_order->bindValue(':username', $username);
        $create_order->bindValue(':product', $product);
        $create_order->bindValue(':amount', $amount);
        $create_order->bindValue(':method', $method);

        if ($create_order->execute())
            return $invoice;
        else
            return false;

    }

    function get_order($invoice) {
        
        global $db;

        $get_order = $db->prepare('SELECT * FROM orders WHERE invoice = :invoice LIMIT 1');
        $get_order->bindValue(':invoice', $invoice);
        $get_order->execute();

        return $get_order->fetch();

    }

?>