<?php

	// server.php
	// Initialize database connection and acquire utility functions.

	date_default_timezone_set('UTC');

	define("SERVER", "localhost");
	define("SENDGRID_API_KEY", "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"); // NOTE: SET THIS

	require dirname(__FILE__) . "/sendgrid/sendgrid-php.php"; // email sending api
    require dirname(__FILE__) . '/chrome_php.php'; // log to chrome console - https://craig.is/writing/chrome-logger
	require dirname(__FILE__) . "/utils.php"; // general utility funcs

	$db_username = "bytesurf_db";
	$db_password = "ad!lb36IUL2mFDf*C75X0#db";
	$db_name = "bytesurf_db";

	try {
		$GLOBALS['db'] = new PDO('mysql:host=' . SERVER . ';dbname=' . $db_name, $db_username, $db_password, [PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
	} catch (PDOException $e) {
    	die('DATABASE INIT ERROR: ' . $e);
	}

?>
