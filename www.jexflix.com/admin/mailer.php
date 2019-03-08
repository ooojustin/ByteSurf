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

    	$sent = send_email(
    		$_POST['subject'], // email subject
    		fill($_POST['message']),  // email message
    		'mailer@jexflix.com', // email to send from
    		'JexFlix', // name to send from
    		$email, // email to send to
    		$email // name to send to
    	);

    	if ($sent)
    		$emails_sent++;

    	// get next email
    	$email = strtok($token);

	}

	die('Sent ' . $emails_sent . ' emails.');

	// replaces all wildcard variables in a message before sending
	function fill($message) {

		// replace trial keys
		preg_match_all('{TRIAL_KEY:\d+}', $message, $matches);
		$matches = current($matches);
		foreach ($matches as $match) {
			$info = explode(':', $match);
			$duration = intval($info[1]);
			$trial_key = generate_trial_key('mailer', $duration);
			$message = str_replace_first('{'.$match.'}', $trial_key, $message);
		}

		// do other stuff...

		return $message;

	}

?>