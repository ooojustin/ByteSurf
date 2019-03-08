<html>
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

    // counters
    $emails_sent = 0;
    $emails_failed = 0;
    $invalid_emails = 0;

	while ($email !== false) {

		// make sure email address is valid
    	if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
    		$invalid_emails++;
    		continue;
    	}

    	/*$response = send_email(
    		$_POST['subject'], // email subject
    		fill($_POST['message']),  // email message
    		'mailer@jexflix.com', // email to send from
    		'JexFlix', // name to send from
    		$email, // email to send to
    		'JexFlix Customer' // name to send to
    	);

    	$status = $response->statusCode();
    	if ($status >= 200 && $status < 300) { // HTTP 2xx == SUCCESS
    		$emails_sent++;
    		echo 'Sent email to: <b>' . $email . '</b><br>' . PHP_EOL;
  	  	} else $emails_failed++;*/
    	// Always set content-type when sending HTML email

    	// email headers
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: <mailer@jexflix.com>' . "\r\n";

		// send email
		$sent = mail($email,$_POST['subject'],fill($_POST['message']),$headers);
		if ($sent) {
			$emails_sent++;
    		echo 'Sent email to: <b>' . $email . '</b><br>' . PHP_EOL;
		} else $emails_failed++;

    	// get next email
    	$email = strtok($token);

	}

	echo '<br>' . PHP_EOL;
	echo 'Sent <b>' . $emails_sent . '</b> emails successfully.<br>' . PHP_EOL;
	echo 'Failed to send <b>' . $emails_failed . '</b> emails.<br>' . PHP_EOL;
	echo 'Found/skipped <b>' . $invalid_emails . '</b> invalid emails.<br>' . PHP_EOL;

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
</html>