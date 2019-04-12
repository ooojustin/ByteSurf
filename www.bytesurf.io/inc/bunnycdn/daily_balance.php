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

    // format numbers before sending email
    $balance = number_format($balance, 2);
    $deposited = number_format($deposited, 2);
    $spent = number_format($spent, 2);

    // determine percent change from yesterday
    $balance_diff = percent_diff_html($data['balance'], $balance);
    $deposited_diff = percent_diff_html($data['deposited'], $deposited);
    $spent_diff = percent_diff_html($data['spent'], $spent);

    // send email notification to support email
    // send_email($subject, $message, $from_email, $from_name, $to_email, $to_name, $reply_to = NULL, $reply_to_name = NULL)
    $subject = 'Daily CDN Balance Update - ' . $yesterday->format('F jS, Y'); // ex: January 1st, 2019
    $message = sprintf(get_paste('KmHqPUvY'), $balance, $balance_diff, $deposited, $deposited_diff, $spent, $spent_diff, $today->getTimestamp());
    send_email($subject, $message, 'cdn@bytesurf.io', 'ByteSurf CDN', 'support@bytesurf.io', 'ByteSurf Staff');

    die('Executed: ' . $today->getTimestamp());

    function percent_diff($a, $b) {
        
        if ($a == $b)
            $p = 0;
        else if ($b == 0)
            $p = -100;
        else
            $p = (1 - ($a / $b))  * 100;
        
        return number_format($p, 2);
        
    }

    function percent_diff_html($a, $b) {
        
        $diff = percent_diff($a, $b);
        
        if ($diff > 0)
            return '<span style="color: #00FF00">(+' . $diff . '%)</span>';
        else if ($diff < 0)
            return '<span style="color: #FF0000">(-' . abs($diff) . '%)</span>';
        else
            return '(0%)';
        
    }

?>