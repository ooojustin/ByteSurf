<?php
	
	require '../inc/server.php';
    require '../inc/session.php';
    require_administrator();

    // check for issues owo
    if (!isset($_POST['email_list']))
    	die('Please provide an email list.');
    else if (filter_var($_POST['email_list'], FILTER_VALIDATE_URL) === FALSE)
    	die('Provided email list is not valid.');
    else if (!isset($_POST['subject']))
    	die('Please provide an email subject.');
    else if (!isset($_POST['message']))
    	die('Please provide an email message.');

    $email_list = @file_get_contents($_POST['email_list']);

    $token = "\r\n"; // token/delim (split by new line)
    $email = strtok($email_list, $token); // init strtok, get first email
    $emails_sent = 0; // keep track of number of emails sent

	while ($email !== false) {

		// make sure email address is valid
    	if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE)
    		continue;

    	// send_email($subject, $message, $from_email, $from_name, $to_email, $to_name)
    	$sent = send_email($_POST['subject'], $_POST['message'], 'mailer@jexflix.com', 'JexFlix', $email, $email);
    	if ($sent)
    		$emails_sent++;

    	// get next email
    	$email = strtok($token);

	}

	die('Sent ' . $emails_sent . ' emails.');

?>