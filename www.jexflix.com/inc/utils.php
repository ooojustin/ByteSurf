<?php
	
	// utils.php
	// Functions used generally in other parts of the website.

	$GLOBALS['ip'] = get_ip();
	// $GLOBALS['ip_info'] = get_ip_info();

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

	function get_movie_data($url) {
		global $db;
		$get_data = $db->prepare('SELECT * FROM movies WHERE url=:url');
    	$get_data->bindValue(':url', $url);
    	$get_data->execute();
   		return $get_data->fetch();

	}

	function authenticated_movie_links($data) {
   		$qualities = json_decode($data['qualities']);
    	foreach ($qualities as $quality)
        	$quality->link = authenticate_cdn_url($quality->link);
      	return $qualities;
	}

	function authenticate_cdn_url($url) {

		// important vars
		global $ip;
		$key = '04187e37-4014-48cf-95f4-d6e6ea6c5094';
		$base_url = 'https://cdn.jexflix.com';

		// determine the path of the file (remove base url)
		$path = str_replace('https://cdn.jexflix.com', '', $url);

		// Set the time of expiry to one day from now
		$expires = time() + (60 * 60 * 24); 

		// establish token data
		$hash_me = $key.$path.$expires.$ip;

		// hash data and generate token
		$token = md5($hash_me, true);
		$token = base64_encode($token);
		$token = strtr($token, '+/', '-_');
		$token = str_replace('=', '', $token);

		// generate new url
		$url = "{$base_url}{$path}?token={$token}&expires={$expires}&ip={$ip}";
		return $url;

	}


	// https://www.virendrachandak.com/techtalk/getting-real-client-ip-address-in-php-2/
	function get_ip() {
    	$ipaddress = '';
    	if (isset($_SERVER['HTTP_CLIENT_IP']))
        	$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        	$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
   		else if(isset($_SERVER['HTTP_X_FORWARDED']))
        	$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    	else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        	$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    	else if(isset($_SERVER['HTTP_FORWARDED']))
        	$ipaddress = $_SERVER['HTTP_FORWARDED'];
    	else if(isset($_SERVER['REMOTE_ADDR']))
        	$ipaddress = $_SERVER['REMOTE_ADDR'];
    	else
        	$ipaddress = 'UNKNOWN';
    	return $ipaddress;
	}

	// using http://ip-api.com/ pro version
	function get_ip_info() {
		$fields = 192511; // generated by http://ip-api.com/docs/api:returned_values#selectable_output
		$access_key = 'IkI7kEr9x3P51qu'; // NOTE TO SELF: GET NEW API KEY
		$url = sprintf('http://pro.ip-api.com/json/%s?key=%s&fields=%s', getIP(), $access_key, $fields);
		$json = file_get_contents($url);
		return json_decode($json, true);
	}

?>