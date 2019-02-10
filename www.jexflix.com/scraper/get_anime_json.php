<?php

require 'inc/safe_request.php';
require 'inc/server.php';
global $db;

define('ENCRYPTION_KEY', 'jexflix');
$sr = new SafeRequest(ENCRYPTION_KEY);

if ($_SERVER['HTTP_USER_AGENT'] != 'jexflix-client')
$sr->output(false, 'invalid request agent.');

$get_json = $db->prepare('SELECT * FROM anime WHERE title=:title');
$get_json->bindValue(':title', $_POST['title']);
$get_json->execute();
$result = $get_json->fetch();

$json_url = $result["data"];

$response = array('url' => $json_url);

$sr->output(true, 'evaluated successfully.', $response);

?>