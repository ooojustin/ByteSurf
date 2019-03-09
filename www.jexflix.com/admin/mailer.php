<html>
<?php
	
    /*
    === WILDCARDS ===
    {TRIAL_KEY:INT} - Generates a trial key. The int value specifies the duration, in seconds.
    {EMAIL_LOCAL_PART} - Returns the local-part of the current email address. (Preceding @)
    */

    // don't limit execution time
    set_time_limit(0);

	require '../inc/server.php';
    require '../inc/session.php';
    require_administrator();

    // store post data
    if (!isset($_GET['post'])) {
        // if we didn't pass 'post' string in url, store post data
        $_GET['post'] = generate_split_string(3, 3);
        $_SESSION['POST_' . $_GET['post']] =  $_POST;
    } else {
        // otherwise, retrieve post data from session
        $_POST = $_SESSION['POST_' . $_GET['post']];
    }

    // check for issues owo
    if (!isset($_POST['email_list']))
    	die('Please provide an email list.');
    else if (filter_var($_POST['email_list'], FILTER_VALIDATE_URL) === FALSE)
    	die('Provided email list is not valid.');
    else if (!isset($_POST['subject']))
    	die('Please provide an email subject.');
    else if (!isset($_POST['message']))
    	die('Please provide an email message.');

    global $email;
    $email_list = @file_get_contents($_POST['email_list']);

    $token = "\r\n"; // token/delim (split by new line)
    $email = strtok($email_list, $token); // init strtok, get first email
    $email_list = array();
    while ($email !== false) {
        array_push($email_list, $email);
        $email = strtok($token);
    }

    // start index
    $start_index = isset($_GET['index']) ? intval($_GET['index']) : 0;

    // end index
    $end_index = $start_index + 99;
    if ($end_index > count($email_list) - 1)
        $end_index = count($email_list) - 1;

    // counters
    $emails_sent = isset($_GET['sent']) ? intval($_GET['sent']) : 0;
    $emails_failed = isset($_GET['failed']) ? intval($_GET['failed']) : 0;
    $invalid_emails = isset($_GET['invalid']) ? intval($_GET['invalid']) : 0;

    for ($i = $start_index; $i <= $end_index; $i++) {

        $email = $email_list[$i];

		// make sure email address is valid
    	if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
    		$invalid_emails++;
    		continue;
    	}

    	$response = send_email(
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
    		// echo 'Sent email to: <b>' . $email . '</b><br>' . PHP_EOL;
  	  	} else $emails_failed++;

		/*$sent = send_email($_POST['subject'], fill($_POST['message']), 'mailer@jexflix.com', $email);
		if ($sent) {
			$emails_sent++;
    		echo 'Sent email to: <b>' . $email . '</b><br>' . PHP_EOL;
		} else $emails_failed++;*/

    	// get next email
    	$email = strtok($token);

	}

    $done = $end_index == count($email_list) - 1;

    if ($done) {
        echo 'Sent <b>' . $emails_sent . '</b> emails successfully.<br>' . PHP_EOL;
        echo 'Failed to send <b>' . $emails_failed . '</b> emails.<br>' . PHP_EOL;
        echo 'Found/skipped <b>' . $invalid_emails . '</b> invalid emails.<br>' . PHP_EOL;
        unset($_SESSION['POST_' . $_GET['post']]);
    } else {
        $url = sprintf('https://jexflix.com/admin/mailer.php?index=%u&sent=%u&failed=%u&invalid=%u&post=%s', $end_index + 1, $emails_sent, $emails_failed, $invalid_emails, $_GET['post']);
        echo '<b>Sent range: </b>' . $start_index . ' - ' . $end_index . '<br>' . PHP_EOL;
        echo '<b>Total handled: </b>' . ($end_index + 1) . '/' . count($email_list) . '<br>' . PHP_EOL;
        echo 'Continuing in <b>3 seconds...</b>';
        die(str_replace('{URL}', $url, '<meta http-equiv="refresh" content="3;url={URL}"/>'));
    }

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

		// replace email local-part
        global $email;
        $local_part = explode('@', $email)[0];
        $message = str_replace('{EMAIL_LOCAL_PART}', $local_part, $message);

		return $message;

	}

?>
</html>