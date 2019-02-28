<?php
	
	define('MERCHANT_ID', 'e9e52a8f8fc84397ff3a71c06301d352');
	define('SECRET', 'tkpVkHD3l0eAedf');

	if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC']))
  		die("No HMAC signature sent");

	$merchant = isset($_POST['merchant']) ? $_POST['merchant'] : '';
	if (empty($merchant))
  		die("No Merchant ID passed");

	if ($merchant != MERCHANT_ID)
  		die("Invalid Merchant ID");

	$request = file_get_contents('php://input');
	if ($request === FALSE || empty($request))
  		die("Error reading POST data");

	$hmac = hash_hmac("sha512", $request, SECRET);
	if ($hmac != $_SERVER['HTTP_HMAC'])
  		die("HMAC signature does not match");

  	

?>