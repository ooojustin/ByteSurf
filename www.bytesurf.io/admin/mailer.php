<html>
<?php
	
    /*
    === WILDCARDS ===
    {TRIAL_KEY:INT} - Generates a trial key. The int value specifies the duration, in seconds.
    {EMAIL_LOCAL_PART} - Returns the local-part of the current email address. (Preceding @)
    */

    // don't limit execution time
    set_time_limit(0);

    // number of emails to send each request
    define('EMAILS_PER_REQUEST', 100);

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
    $start_index = get_post_int('index');

    // end index
    $end_index = $start_index + EMAILS_PER_REQUEST - 1; // remove 1 because we include end_index

    // clamp end index to the last email in the list
    $last_index = count($email_list) - 1;
    $end_index = ($end_index > $last_index) ? $last_index : $end_index;

    // counters
    $emails_sent = get_post_int('sent');
    $emails_failed = get_post_int('failed');
    $invalid_emails = get_post_int('invalid');

    for ($i = $start_index; $i <= $end_index; $i++) {

        $email = $email_list[$i];

		// make sure email address is valid
    	if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
    		$invalid_emails++;
    		continue;
    	}

        // log email before we send it
        log_email($email, $_POST['subject'], 'Mass Mailer');

    	$response = send_email(
    		$_POST['subject'], // email subject
    		fill($_POST['message']),  // email message
    		'mailer@jexflix.com', // email to send from
    		'JexFlix', // name to send from
    		$email, // email to send to
    		'JexFlix Customer' // name to send to
    	);

    	$status = $response->statusCode();
    	if ($status >= 200 && $status < 300) // HTTP 2xx == SUCCESS
    		$emails_sent++;
  	  	else 
            $emails_failed++;

    	// get next email
    	$email = strtok($token);

	}

    // if the end index was the 
    $done = $end_index == $last_index;

    if ($done) {

        echo 'Sent <b>' . $emails_sent . '</b> emails successfully.<br>' . PHP_EOL;
        echo 'Failed to send <b>' . $emails_failed . '</b> emails.<br>' . PHP_EOL;
        echo 'Found/skipped <b>' . $invalid_emails . '</b> invalid emails.<br>' . PHP_EOL;
        unset($_SESSION['POST_' . $_GET['post']]);

    } else {

        // send request to mail with post data id
        $url = 'https://jexflix.com/admin/mailer.php?post=' . $_GET['post'];

        // update data in session before redirecting
        $_POST['index'] = $end_index + 1;
        $_POST['sent'] = $emails_sent;
        $_POST['failed'] = $emails_failed;
        $_POST['invalid'] = $invalid_emails;
        $_SESSION['POST_' . $_GET['post']] = $_POST;

        // output information and redirect to next portion of emails
        echo '<b>Sent range: </b>' . $start_index . ' - ' . $end_index . '<br>' . PHP_EOL;
        echo '<b>Total handled: </b>' . ($end_index + 1) . '/' . count($email_list) . '<br>' . PHP_EOL;
        echo 'Continuing in <b>3 seconds...</b>';
        die(str_replace('{URL}', $url, '<meta http-equiv="refresh" content="3;url={URL}"/>'));

    }

    function get_post_int($var) {
        return isset($_POST[$var]) ? $_POST[$var] : 0;
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