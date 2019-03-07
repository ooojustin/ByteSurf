<?php
	
	// server.php
	// Initialize database connection and acquire utility functions.
	
	define("SERVER", "localhost");
	define("SENDGRID_API_KEY", ""); // NOTE: SET THIS

	require dirname(__FILE__) . "/sendgrid/sendgrid-php.php";
	require dirname(__FILE__) . "/utils.php";

	$db_username = "jexflixc_admin";
	$db_password = "K+VLZP;x{G%Q";
	$db_name = "jexflixc_db";
	
	try {
		$GLOBALS['db'] = new PDO('mysql:host=' . SERVER . ';dbname=' . $db_name, $db_username, $db_password, [PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
	} catch (PDOException $e) {
    	die('DATABASE INIT ERROR: ' . $e);
	}

?>