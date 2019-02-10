<?php

	require 'inc/safe_request.php';
	require 'inc/server.php';
	global $db, $sr;

	if ($_SERVER['HTTP_USER_AGENT'] != 'jexflix-client')
		$sr->output(false, 'invalid request agent.');

	$get_json = $db->prepare('SELECT * FROM anime WHERE title=:title');
	$get_json->bindValue(':title', $_POST['title']);
	$get_json->execute();
	$result = $get_json->fetch();

	$response['url'] = $result["data"];
	$sr->output(true, 'evaluated successfully.', $response);

?>