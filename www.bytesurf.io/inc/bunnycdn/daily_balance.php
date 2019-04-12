<?php
    
    require '../server.php';
    require 'bunnycdn.php';
    global $db;

    define('BUNNYCDN_USER_AGENT', 'ByteSurf.io Server');
    $bcdn = new BunnyCDN($_GET['key'], BUNNYCDN_USER_AGENT);
    
    // determine current balance on bunnycdn
    $balance = $bcdn->get_balance();

    // get today into DateTime object
    $today = new DateTime();
    $today->setTime(0, 0, 0);

    // get the amount deposited
    $yesterday = new DateTime();
    $yesterday->add(DateInterval::createFromDateString('yesterday'));
    $deposited = $bcdn->get_deposited_on_day($yesterday);
    
    // get the most recent information in cdn_balance table
    $get_data = $db->prepare('SELECT * FROM cdn_balance ORDER BY id DESC LIMIT 1');
    $get_data->execute();
    $data = $get_data->fetch();

    // determine the amount spent yesterday
    // this can be determined by substracing (current balance + deposits) from the balance from yesterday
    $spent = $data['balance'] - ($balance - $deposited);
    
    // add new row to database
    $add_data = $db->prepare('INSERT INTO cdn_balance (timestamp, balance, deposited, spent) VALUES (?, ?, ?, ?)');
    $add_data->bindValue(1, $today->getTimestamp());
    $add_data->bindValue(2, $balance);
    $add_data->bindValue(3, $deposited);
    $add_data->bindValue(4, $spent);
    $add_data->execute();

    die('Executed: ' . $today->getTimestamp());

?>