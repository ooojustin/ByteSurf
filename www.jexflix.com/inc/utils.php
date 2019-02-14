<?php
	
	// utils.php
	// Functions used generally in other parts of the website.

	function login($email, $password) {
		global $db;
		$check_login = $db->prepare('SELECT * FROM users WHERE email=:email');
		$check_login->bindValue(':email', $email);
		$check_login->execute();
		$user = $check_login->fetch();
		return $user && password_verify($password, $user['password']);
	}

	function create_account($email, $password) {
		global $db;
		$create_account = $db->prepare('INSERT INTO users (email, password) VALUES (:email, :password)');
		$create_account->bindValue(':email', $email);
		$create_account->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
		$create_account->execute();
	}

?>