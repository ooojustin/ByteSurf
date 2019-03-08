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

		// create account
		$create_account = $db->prepare('INSERT INTO users (email, username, password) VALUES (:email, :username, :password)');
		$create_account->bindValue(':email', $email);
		$create_account->bindValue(':username', $username);
		$create_account->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
		$create_account->execute();

		// generate registration code
		$code = generate_split_string(3, 3);

		// log registration
		$log_registration = $db->prepare('INSERT INTO registrations (username, email, code, timestamp) VALUES (:username, :email, :code, :timestamp)');
		$log_registration->bindValue(':username', $username);
		$log_registration->bindValue(':email', $email);
		$log_registration->bindValue(':code', $code);
		$log_registration->bindValue(':timestamp', time());
		$log_registration->execute();

		// send verification email
		// ... TODO ...

	}


	function update_password($username, $old_password, $new_password) {
	    global $db;    
	    // check password is correct with login function
	    if (!login($username, $old_password)) 
	    	return false;    
	    return update_password_nocheck($username, $new_password);
	}


	function update_password_nocheck($username, $new_password) {
	    global $db;   
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

	function update_expires($username, $expires) {
		global $db;
		$update_expires = $db->prepare('UPDATE users SET expires=:expires WHERE username=:username');
		$update_expires->bindValue(':username', $username);
		$update_expires->bindValue(':expires', $expires);
		return $update_expires->execute();
	}

	function add_subscription_time($username, $duration) {
		$expires = intval(get_user($username)['expires']);
		if ($expires > time())
			$duration += $expires - time();
		update_expires($username, time() + $duration);
	}

	function get_trial_key($trial_key) {
		global $db;
		$get_trial_key = $db->prepare('SELECT * FROM trial_keys WHERE trial_key=:trial_key');
		$get_trial_key->bindValue(':trial_key', $trial_key);
		$get_trial_key->execute();
		return $get_trial_key->fetch();
	}
	
	function generate_trial_key($username, $duration) {
		global $db;
		$trial_key = generate_split_string(5, 4);
		$generate_trial_key = $db->prepare('INSERT INTO trial_keys (trial_key, owner, duration) VALUES (:trial_key, :owner, :duration)');
		$generate_trial_key->bindValue(':trial_key', $trial_key);
		$generate_trial_key->bindValue(':owner', $username);
		$generate_trial_key->bindValue(':duration', $duration);
		return $generate_trial_key->execute() ? $trial_key : false;
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

	function reseller_received_payment($username) {
		global $db;
		$update_time = $db->prepare('UPDATE resellers SET last_purchase=:last_purchase WHERE username=:username');
		$update_time->bindValue(':username', $username);
		$update_time->bindValue(':last_purchase', time());
		return $update_time->execute();
	}

	function get_reseller($username) {
		global $db;
		$get_reseller = $db->prepare('SELECT * FROM resellers WHERE username=:username LIMIT 1');
		$get_reseller->bindValue(':username', $username);
		$get_reseller->execute();
		return $get_reseller->fetch();
	}

	function update_reseller($username, $selly_email, $selly_api_key) {
		global $db;
		$query = get_reseller($username) ?
			"UPDATE resellers SET selly_email=:selly_email, selly_api_key=:selly_api_key WHERE username=:username" : // update query
			"INSERT INTO resellers (username, selly_email, selly_api_key) VALUES (:username, :selly_email, :selly_api_key)"; // insert query
		$update_reseller = $db->prepare($query);
		$update_reseller->bindValue(':username', $username);
		$update_reseller->bindValue(':selly_email', $selly_email);
		$update_reseller->bindValue(':selly_api_key', $selly_api_key);
		return $update_reseller->execute();
	}

	// adds $ to a resellers account
	function add_reseller_balance($username, $amount) {
		global $db;
		$reseller = get_reseller($username);
		if (!$reseller)
			return false;
		$new_amount = $reseller['balance'] + $amount;
		$update_balance = $db->prepare('UPDATE resellers SET balance=:balance WHERE username=:username');
		$update_balance->bindValue(':username', $username);
		$update_balance->bindValue(':balance', $new_amount);
		return $update_balance->execute();
	}

	// removes $ from a resellers account (aka add negative balance, lol)
	function remove_reseller_balance($username, $amount) {
		$amount = -$amount;
		return add_reseller_balance($username, $amount);
	}

	// gets the next reseller in the priority queue for a specified product price
	function get_next_reseller($price) {
		$resellers = get_reseller_list($price);
		while ($reseller = array_shift($resellers))
			if (reseller_is_valid($reseller))
				return $reseller;
		return false;
	}

	// confirms resellers selly email & api key (test api call)
	function reseller_is_valid($reseller) {
		if (!$reseller)
			return false;
		require dirname(__FILE__) . '/selly/selly.php';
		$selly = new SellyAPI($reseller['selly_email'], $reseller['selly_api_key']);
		if (!$selly->is_valid()) {
			update_reseller($reseller['username'], '', '');
			return false;
		} else return true;
	}

	// gets a list of resellers available for an amount (sorted by last_purchase low to high)
	function get_reseller_list($price) {
		global $db;
		$min_balance = $price * 0.75; // minimum reseller balance for this transaction
		$get_resellers = $db->prepare('SELECT * FROM resellers WHERE balance>=:min_balance AND LENGTH(selly_email) > 0 ORDER BY last_purchase ASC');
		$get_resellers->bindValue(':min_balance', $min_balance);
		$get_resellers->execute();
		return $get_resellers->fetchAll();
	}

	// gets the priority of a specific reseller at a specified price
	function get_reseller_priority($username, $price) {
		$ahead = 0; // number of resellers ahead of current user
		$resellers = get_reseller_list($price);
		foreach ($resellers as $reseller) {
			if ($reseller['username'] == $username)
				return $ahead + 1;
			else
				$ahead++;
		}
		return -1; // specified user is invalid
	}

	// gets all orders from a specific user w/ data that can be displayed
	function get_orders($username) {

		global $db;
		$orders = array();

		// btc orders
		$get_orders_btc = $db->prepare('SELECT * FROM orders_btc WHERE username=:username AND status=\'completed\'');
		$get_orders_btc->bindValue(':username', $username);
		$get_orders_btc->execute();
		while ($order = $get_orders_btc->fetch()) {
			$order = array('invoice' => $order['invoice'], 'product' => $order['product'], 'amount' => $order['amount_usd']);
			array_push($orders, $order);
		}

		// paypal orders
		$get_orders_pp = $db->prepare('SELECT * FROM orders_pp WHERE username=:username AND status=\'completed\'');
		$get_orders_pp->bindValue(':username', $username);
		$get_orders_pp->execute();
		while ($order = $get_orders_pp->fetch()) {
			$order = array('invoice' => $order['invoice'], 'product' => $order['product'], 'amount' => $order['amount']);
			array_push($orders, $order);
		}

		return $orders;

	}

	/*function send_email($subject, $message, $from_email, $from_name, $to_email, $to_name) {
		$sendgrid = new \SendGrid(SENDGRID_API_KEY); // defined in server.php
        $email = new \SendGrid\Mail\Mail(); 
        $email->setFrom($from_email, $from_name);
        $email->setSubject($subject);
        $email->addTo($to_email, $to_name);
        $email->addContent("text/html", $message);
        return $sendgrid->send($email);
	}*/

	function send_email($subject, $message, $from_email, $to_email) {
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: <' . $from_email . '>' . "\r\n";
		return mail($to_email, $subject, $message, $headers);
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
		global $ip, $ip_info;
		if (!isset($ip_info)) {
			$fields = 192511; // generated by http://ip-api.com/docs/api:returned_values#selectable_output
			$access_key = 'IkI7kEr9x3P51qu'; // NOTE TO SELF: GET NEW API KEY
			$url = sprintf('http://pro.ip-api.com/json/%s?key=%s&fields=%s', $ip, $access_key, $fields);
			$json = file_get_contents($url);
			$ip_info = json_decode($json, true);
		}
		return $ip_info;
	}

	// return time string (local time tho, so its meant for display)
	function get_time_string($timestamp) {
		$ip_info = get_ip_info();
		$date = new DateTime('now', new DateTimeZone($ip_info['timezone']));
		$date->setTimestamp($timestamp);
		return $date->format('F j, Y, g:i a');
	}

	function contains($needle, $haystack)	{
    	return strpos($haystack, $needle) !== false;
	}

	function generate_split_string($xC, $xY) {
		// length = (x * y) + y - 1
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chars_length = strlen($chars);
        $str = '';
        for ($x = 0; $x < $xC; $x++) {
            for ($y = 0; $y < $xY; $y++) {
                $str .= $chars[rand(0, $chars_length - 1)];
            }
            $str .= '-';
        }
        return substr($str, 0, -1);
    }

    function str_replace_first($search, $replace, $subject) {
    	$pos = strpos($subject, $search);
		if ($pos !== false)
   			return substr_replace($subject, $replace, $pos, strlen($search));
   		else
   			return $subject;
    }

?>