<?php

    if (!isset($GLOBALS['db']))
        require dirname(__FILE__) . '/../server.php';
    
    function create_order($user, $amount, $method) {

        global $db;

        $invoice = generate_invoice_string();

        $create_order = $db->prepare('INSERT INTO orders (invoice, user, amount, method) VALUES (:invoice, :user, :amount, :method)');
        $create_order->bindValue(':invoice', $invoice);
        $create_order->bindValue(':user', $user);
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

    function generate_invoice_string() {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chars_length = strlen($chars);
        $invoice = '';
        for ($x = 0; $x < 5; $x++) {
            for ($y = 0; $y < 4; $y++) {
                $invoice .= $chars[rand(0, $chars_length - 1)];
            }
            $invoice .= '-';
        }
        return substr($invoice, 0, -1);
    }

?>