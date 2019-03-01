<?php
	
	// utils.php
	// Functions used generally in other parts of the website.
	
	// https://stackoverflow.com/a/6768831/5699643
	$GLOBALS['current_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	// clients ip address
	$GLOBALS['ip'] = get_ip();

	function login($username, $password) {
		global $db;
		$check_login = $db->prepare('SELECT * FROM users WHERE username=:username');
		$check_login->bindValue(':username', $username);
		$check_login->execute();
		$user = $check_login->fetch();
		return $user && password_verify($password, $user['password']);
	}

	function create_account($username, $email, $password) {
		global $db;
		$create_account = $db->prepare('INSERT INTO users (email, username, password) VALUES (:email, :username, :password)');
		$create_account->bindValue(':email', $email);
		$create_account->bindValue(':username', $username);
		$create_account->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
		return $create_account->execute();
	}

	function update_password($username, $old_password, $new_password) {

	    global $db;
	    
	    // check password is correct with login function
	    if (!login($username, $old_password)) 
	    	return false;
	    
	    $update_password = $db->prepare('UPDATE users SET password=:password WHERE username=:username');
	    $update_password->bindValue(':username', $username);
	    $update_password->bindValue(':password', password_hash($new_password, PASSWORD_BCRYPT));
	    return $update_password->execute();

	}
	
	function update_picture($username, $pfp) {

		global $db;

		$update_picture = $db->prepare('UPDATE users SET pfp=:pfp WHERE username=:username');
		$update_picture->bindValue(':username', $username);
		$update_picture->bindValue(':pfp', $pfp);
		return $update_picture->execute();

	}
	
	function get_user($username) {
		global $db;
		$get_data = $db->prepare('SELECT * FROM users WHERE username=:username');
		$get_data->bindValue(':username', $username);
		$get_data->execute();
		return $get_data->fetch(); 
	}
	
	
	function get_user_by_id($id) {
		global $db;
		$get_data = $db->prepare('SELECT * FROM users WHERE id=:id');
		$get_data->bindValue(':id', $id);
		$get_data->execute(); 	
		return $get_data->fetch(); 
	}

	function get_user_by_email($email) {
		global $db;
		$check_email = $db->prepare('SELECT * FROM users WHERE email=:email');
		$check_email->bindValue(':email', $email);
	    $check_email->execute();
	    return $check_email->fetch();
	}

	function get_movie_data($url) {
		global $db;
		$get_data = $db->prepare('SELECT * FROM movies WHERE url=:url');
    	$get_data->bindValue(':url', $url);
    	$get_data->execute();
   		return $get_data->fetch();
	}

	function get_imdb_rating($url) {
		$movie_data = get_movie_data($url);
		$imdb_url = 'https://www.imdb.com/title/' . $movie_data['imdb_id'];
		$imdb_raw = file_get_contents($imdb_url);
		$regex = '/<span class="rating">.*<span class="ofTen">\/10<\/span><\/span>/m';
		if (!preg_match($regex, $imdb_raw, $matches))
			return doubleval(-1);
		// forgive me lord for i have sinned:
		return doubleval(explode('<', explode('>', $matches[0])[1])[0]);
	}

	function update_imdb_rating($url) {
		global $db;
		if (needs_imdb_update($url)) {
			// get current rating from imdb
			$rating = get_imdb_rating($url);
			// store updated rating
			$update_imdb_rating = $db->prepare('UPDATE movies SET rating=:rating WHERE url=:url');
			$update_imdb_rating->bindValue(':rating', $rating);
			$update_imdb_rating->bindValue(':url', $url);
			$update_imdb_rating->execute();
			// log update
			$log_imdb_update = $db->prepare('INSERT INTO imdb_updates (url, timestamp) VALUES (:url, :timestamp)');
			$log_imdb_update->bindValue(':url', $url);
			$log_imdb_update->bindValue(':timestamp', time());
			$log_imdb_update->execute();
		}
	}

	function needs_imdb_update($url) {
		global $db;
		$get_last_update = $db->prepare('SELECT * FROM imdb_updates WHERE url=:url ORDER BY id DESC LIMIT 1');
		$get_last_update->bindValue(':url', $url);
		$get_last_update->execute();
		$last_update = $get_last_update->fetch();
		if (!$last_update)
			return true;
		$day_ago = time() - (60 * 60 * 24); // timestamp 1 day ago
		return $last_update['timestamp'] < $day_ago; // return true if last update was over a day ago
	}

	function authenticated_movie_links($data) {
   		$qualities = json_decode($data['qualities']);
    	foreach ($qualities as $quality)
        	$quality->link = authenticate_cdn_url($quality->link);
      	return $qualities;
	}

	function authenticate_cdn_url($url, $is_server_request = false) {

		// important vars
		$ip = $is_server_request ? $_SERVER['SERVER_ADDR'] : $GLOBALS['ip'];
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
		global $ip_info;
		if (!isset($ip_info)) {
			$fields = 192511; // generated by http://ip-api.com/docs/api:returned_values#selectable_output
			$access_key = 'IkI7kEr9x3P51qu'; // NOTE TO SELF: GET NEW API KEY
			$url = sprintf('http://pro.ip-api.com/json/%s?key=%s&fields=%s', getIP(), $access_key, $fields);
			$json = file_get_contents($url);
			$ip_info = json_decode($json, true);
		}
		return $ip_info;
	}

	// return time string (local time tho, so its meant for display)
	function get_time_string($timestamp) {
		$ip_info = get_ip_info();
		$date = new DateTime();
		$date->setTimezone(new DateTimeZone($ip_info['timezone']));
		$date->setTimestamp($timestamp);
		return $date->format('F j, Y, g:i a');
	}

	function contains($needle, $haystack)	{
    	return strpos($haystack, $needle) !== false;
	}

?>