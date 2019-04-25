<?php
	
	// utils.php
	// Functions used generally in other parts of the website.
	
	// https://stackoverflow.com/a/6768831/5699643
	$GLOBALS['current_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	// clients ip address
	$GLOBALS['ip'] = get_ip();

	function msg($title, $message, $btn_text = 'GO BACK', $btn_link = NULL) {
		$_SESSION['msg'] = array(
			'title' => $title,
			'message' => $message,
            'btn_text' => $btn_text,
            'btn_link' => $btn_link
		);
		header('location: https://bytesurf.io/msg');
		die();
	}

	// rebuilt file_get_contents
	// parses given url, rebuilds it using parse_url and http_build_query
	// automatically ensures that params are encoded properly to prevent errors
	function file_get_contents_fixed($url) {
		if (!filter_var($url, FILTER_VALIDATE_URL))
			throw new Exception('Invalid URL provided to file_get_contents_fixed.');
		$parsed = parse_url($url);
		parse_str($parsed['query'], $query_params);
		$query = http_build_query($query_params);
		$url = sprintf('%s://%s%s?%s', $parsed['scheme'], $parsed['host'], $parsed['path'], $query);
		$data = file_get_contents($url);
		return $data;
  }

	function get_request($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	function login($username, $password) {
		global $db;
		$check_login = $db->prepare('SELECT * FROM users WHERE username=:username');
		$check_login->bindValue(':username', $username);
		$check_login->execute();
		$user = $check_login->fetch();
		return $user && password_verify($password, $user['password']);
	}

	function create_account($username, $email, $password, $referrer = NULL) {

		global $db, $ip;

		// create account
		$create_account = $db->prepare('INSERT INTO users (email, username, password) VALUES (:email, :username, :password)');
		$create_account->bindValue(':email', $email);
		$create_account->bindValue(':username', $username);
		$create_account->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
		$create_account->execute();

		// generate registration code
		$code = generate_split_string(3, 3);

		// log registration
		$log_registration = $db->prepare('INSERT INTO registrations (username, email, code, referrer, ip_address, timestamp) VALUES (:username, :email, :code, :referrer, :ip_address, :timestamp)');
		$log_registration->bindValue(':username', $username);
		$log_registration->bindValue(':email', $email);
		$log_registration->bindValue(':code', $code);
		$log_registration->bindValue(':referrer', $referrer);
		$log_registration->bindValue(':ip_address', $ip);
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

		// get current expiration time
		$expires = intval(get_user($username)['expires']);

		// ignore call if user is already lifetime
		if ($expires == -1)
			return;

		// if their subscription isnt expired yet, add remaining time to duration
		if ($expires > time())
			$duration += $expires - time();

		// set expiration time to current timestamp + subscription duration
		update_expires($username, time() + $duration);
		
	}

	function get_trial_key($trial_key) {
		global $db;
		$get_trial_key = $db->prepare('SELECT * FROM trial_keys WHERE trial_key=:trial_key');
		$get_trial_key->bindValue(':trial_key', $trial_key);
		$get_trial_key->execute();
		return $get_trial_key->fetch();
	}

	function trial_key_exists($trial_key) {
		global $db;
		$get_trial_key = $db->prepare('SELECT * FROM trial_keys WHERE trial_key=:trial_key');
		$get_trial_key->bindValue(':trial_key', $trial_key);
		$get_trial_key->execute();
		return $get_trial_key->rowCount() > 0;
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
	
	function get_series_data($url) {
		global $db;
		$get_data = $db->prepare('SELECT * FROM series WHERE url=:url');
    	$get_data->bindValue(':url', $url);
    	$get_data->execute();
   		return $get_data->fetch();
	}
	
    function get_anime_data($url) {
        global $db;
        $get_data = $db->prepare('SELECT * FROM anime WHERE url=:url');
        $get_data->bindValue(':url', $url);   
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

		// return if we don't have reseller
		if (!$reseller)
			return false;

		// include selly file, if class is undefined
		if (!class_exists('SellyAPI'))
			require dirname(__FILE__) . '/selly/selly.php';

		// init SellyAPI class, check validity
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
			else if (reseller_is_valid($reseller))
				$ahead++;
		}
		return -1; // specified user is invalid
	}

    function get_active_party() {
        if (!isset($_SESSION['party']))
            return NULL;
        return get_party($_SESSION['party']);   
    }

    function create_party($title = NULL, $type = NULL, $season = -1, $episode = -1) {
        
        global $db, $user;
        if (!$user)
            return NULL;
        
        // generate party indenfitier
        $party = generate_split_string(3, 3);
        
        // params for bind_content_values
        $params = array('t' => $title ?: '', 'type' => $type ?: '', 's' => $season, 'e' => $episode);
        
        $create_party = $db->prepare('INSERT INTO parties (party, owner, users, type, title, season, episode, timestamp) VALUES (:party, :owner, :users, :type, :title, :season, :episode, :timestamp)');
        bind_content_values($create_party, $params);
        $create_party->bindValue(':party', $party);
        $create_party->bindValue(':owner', $user['username']);
        $create_party->bindValue(':users', '[]');
        $create_party->bindValue(':timestamp', time_ms());
        $create_party->execute();
        
        $_SESSION['party'] = $party;
        return $party;
        
    }
    
    function get_party($party) {
        global $db;
        $get_party = $db->prepare('SELECT * FROM parties WHERE party=:party ORDER BY id DESC LIMIT 1');
        $get_party->bindValue(':party', $party);
        $get_party->execute();
        return $get_party->fetch();
    }
    
    function get_active_party_url() {
        
        // get party data, make sure it's valid
        $party = get_active_party();
        if (!$party || empty($party['title']))
            return false;
        
        // build array of url params
        $params = array('t' => $party['title']);
        if ($party['type'] == 'show')
            $params['s'] = $party['season'];
        if ($party['type'] == 'anime' || $party['type'] == 'show')
            $params['e'] = $party['episode'];
        
        // build query and generate url
        $query = http_build_query($params);
        $url = sprintf('https://bytesurf.io/%s.php?%s', $party['type'], $query);
        
        return $url;
        
    }

    function initialize_party_system($public_html = '') {
        
        // make sure the user is in a valid party
        $party = get_active_party();
        if (!$party)
            return;
        
        // set type & s & e, so we can compare to values in row
        $_GET['type'] = get_type();
        default_param('s', -1);
        default_param('e', -1);
        
        // determine whether or not we're on the correct page
        $correct = true;
        if ($_GET['type'] != $party['type'])
            $correct = false;
        else if ($_GET['t'] != $party['title'])
            $correct = false;
        else if ($_GET['s'] != $party['season'])
            $correct = false;
        else if ($_GET['e'] != $party['episode'])
            $correct = false;
        
        // if we're not on the right page (and we don't own the party), redirect
        // note: only do this when 'type' is valid. this will allow the user to visit the home page normally, even in a party.
        if (!$correct && !is_party_owner() && validate_type($_GET['type'])) {
            if ($correct_url = get_active_party_url()) {
                header('location: ' . $correct_url);
                die();
            }
        }
        
        // include party script
        echo '<script src="' . $public_html . 'js/parties.js"></script>' . PHP_EOL;
        
        
    }

    // sends a chat message to an active party
    function send_party_chat_message($message) {
        
        // make sure we're in a party
        $party = get_active_party();
        if (!$party)
            return false;
        
        // make sure we're logged in
        global $user;
        if (!$user)
            return false;
        
        // make sure message is a reasonable size
        if (strlen($message) > 1024)
            return false;
        
        global $db;
        $send_message = $db->prepare('INSERT INTO parties_chat (party, username, message, timestamp) VALUES (:party, :username, :message, :timestamp)');
        $send_message->bindValue(':party', $party['party']);
        $send_message->bindValue(':username', $user['username']);
        $send_message->bindValue(':message', $message);
        $send_message->bindValue(':timestamp', time());
        return $send_message->execute();
        
    }

    // gets new messages from a party
    function get_party_chat_messages($last_id = -1) {
        global $db;
        $get_messages = $db->prepare('SELECT * FROM parties_chat WHERE party = :party AND id > :last_id ORDER BY id DESC');
        $get_messages->bindValue(':party', $_SESSION['party']);
        $get_messages->bindValue(':last_id', $last_id);
        return $get_messages->fetchAll();
    }
    
    // updates 'users' colum in a specified party (from an array, key = usernae & value = timestamp)
    function update_party_users($users) {
        global $db;
        $update_party = $db->prepare('UPDATE parties SET users=:users WHERE party=:party');
        $update_party->bindValue(':users', json_encode($users));
        $update_party->bindValue(':party', $_SESSION['party']);
        return $update_party->execute();
    }

    // updates timestamp/time/playing from the host of a party
    function update_party($timestamp, $time, $playing) {
        global $db;
        $update_party = $db->prepare('UPDATE parties SET type=:type, title=:title, season=:season, episode=:episode, timestamp=:timestamp, time=:time, playing=:playing WHERE party=:party');
        bind_content_values($update_party);
        $update_party->bindValue(':party', $_SESSION['party']);
        $update_party->bindValue(':timestamp', $timestamp);
        $update_party->bindValue(':time', $time);
        $update_party->bindValue(':playing', $playing);
        return $update_party->execute();
    }

    // returns whether or not logged in user is the party owner
    function is_party_owner($username = NULL) {
        global $user;
        $username = $username ?: $user['username'];
        $owner = get_active_party()['owner'];
        return strtolower($username) == strtolower($owner);
    }

    // returns the type of content, based on current url
    // (ex: /show.php = show, /movie.php = movie, /anime.php = anime)
    function get_type($url = NULL) {
        $url = $url ?: $GLOBALS['current_url'];
        $path = parse_url($url, PHP_URL_PATH);
        $type = pathinfo($path, PATHINFO_FILENAME);
        return $type;
    }

	// code to log sent emails
	function log_email($address, $subject, $type) {
		global $db;
		$insert_email = $db->prepare('INSERT INTO emails (address, subject, type, timestamp) VALUES (:address, :subject, :type, :timestamp)');
		$insert_email->bindValue(':address', $address);
		$insert_email->bindValue(':subject', $subject);
		$insert_email->bindValue(':type', $type);
		$insert_email->bindValue(':timestamp', time());
		return $insert_email->execute();
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

	function send_email($subject, $message, $from_email, $from_name, $to_email, $to_name, $reply_to = NULL, $reply_to_name = NULL) {

		// initialize sendgrid & email
		$sendgrid = new \SendGrid(SENDGRID_API_KEY); // defined in server.php
        $email = new \SendGrid\Mail\Mail(); 

        // default variables
        $email->setFrom($from_email, $from_name);
        $email->setSubject($subject);
        $email->addTo($to_email, $to_name);
        $email->addContent("text/html", $message);

        // optional stuff
        if (!is_null($reply_to) && !is_null($reply_to_name))
        	$email->setReplyTo($reply_to, $reply_to_name);

        return $sendgrid->send($email);

	}

	function get_user_registration($username) {
		global $db;
		$get_user_registration = $db->prepare('SELECT * FROM registrations WHERE username = :username');
		$get_user_registration->bindValue(':username', $username);
		$get_user_registration->execute();
		return $get_user_registration->fetch();
	}

	function get_referred_users($username, $paid_only = false) {

		global $db;

		$get_referred_users = $db->prepare('SELECT * FROM registrations WHERE referrer = :referrer');
		$get_referred_users->bindValue(':referrer', $username);
		$get_referred_users->execute();

		if (!$paid_only)
			return $get_referred_users->rowCount();

		$paid_users = 0;
		while ($user = $get_referred_users->fetch()) {
			$username = $user['username'];
			$orders = get_orders($username);
			if (count($orders) > 0)
				$paid_users++;
		}

		return $paid_users;

	}

    // returns an array of progress_tracker records, full list of all last completed/uncompleted episodes from diff shows
    function get_progress_tracker_data($completed = false) {  
        global $user, $db;
        if (!$user)
            return array();        
        $get_watching = $db->prepare('SELECT * FROM progress_tracker WHERE username=:username AND completed=:completed');
        $get_watching->bindValue(':username', $user['username']);
        $get_watching->bindValue(':completed', intval($completed));
        $get_watching->execute();
        return $get_watching->fetchAll();
    }

    // returns an array of progress_tracker records
    // same thing as get_progress_tracker_data but returns 1 record per show (highest season/episode)
    function get_watching_list($completed = false) {      
        $list = array();
        foreach (get_progress_tracker_data($completed) as $watching) {
            $title = $watching['title'];
            $type = $watching['type'];
            $item = sprintf('%s:%s', $watching['type'], $watching['title']);
            if (array_key_exists($item, $list))
                continue;
            $list[$item] = get_furthest_episode($watching['title'], $watching['type'], $completed);
        }
        return $list;                        
    }

    // puts progress tracker data into strings (format - type:title:season:episode)
    // works on get_progress_tracker_data or get_watching_list
    function stringify_progress_tracker_data($list) {
        $list_str = array();
        foreach ($list as $item) {
            $item_str = sprintf('%s:%s:%s:%s', $item['type'], $item['title'], $item['season'], $item['episode']);
            array_push($list_str, $item_str);
        }
        return $list_str;
    }
    
    // checks if a specified episode was watched
    function is_watched($title = NULL, $type = NULL, $season = -1, $episode = -1) {
        if (is_null($title)) {
            $title = $_GET['t'];
            $type = get_type();
            $season = $_GET['s'];
            $episode = $_GET['e'];
        }
        $item_str = sprintf('%s:%s:%s:%s', $type, $title, $season, $episode);
        $watched_list = get_progress_tracker_data(true, true);
        $watched_list_str = stringify_progress_tracker_data($watched_list);
        $watched = in_array($item_str, $watched_list_str);
        return $watched;
    }

    // automatically binds type/s/e/t to a given statement (query object)
    function bind_content_values($query, $arr = NULL) {
        $arr = is_null($arr) ? $_GET : $arr;
        $query->bindValue(':type', $arr['type']);
        $query->bindValue(':title', $arr['t']);
        $query->bindValue(':season', $arr['s']);
        $query->bindValue(':episode', $arr['e']);
    }

    function validate_type($type, $die_if_invalid = false) {
        $types = array('movie', 'show', 'anime');
        $valid = in_array($type, $types);
        $msg = 'Invalid type provided: ' . $type;
        if (!$valid && $die_if_invalid)
            die_gz($msg);
        return $valid;
    }

    function require_get_params($params) {
        foreach ($params as $param)
            if (!isset($_GET[$param]))
                die_gz('Missing required parameter: ' . $param);
    }

    function default_param($param, $value, &$arr = NULL) {
        $use_get = is_null($arr);
        $arr = $use_get ? $_GET : $arr;
        if (!isset($arr[$param]))
            $arr[$param] = $value;
        if ($use_get)
            $_GET = $arr;
    }

    function get_progress($username) {
        global $db;
        $get_progress = $db->prepare('SELECT * FROM progress_tracker WHERE username=:username AND title=:title AND type=:type AND season=:season AND episode=:episode ORDER BY id DESC LIMIT 1');
        bind_content_values($get_progress);
        $get_progress->bindValue(':username', $username);
        $get_progress->execute();
        return $get_progress->fetch();
    }

    function is_queued($type, $title) {
        $queue = get_queue();
        $item = sprintf('%s:%s', $type, $title);
        return in_array($item, $queue);
    }

	function get_queue($get_data = false) {
        
        global $user;
        if (!$user)
            return array();

        // get raw queue info and make sure its not null
        $queue = $user['queue'];
        if (is_null($queue))
            return array();

		// list of movie urls
		$queue = json_decode($queue, true);

		// return list of urls, if necessary
		if (!$get_data)
			return $queue;

		// otherwise, convert urls to an array of movies/animes/shows
		$item_list = array();
		foreach ($queue as $queue_item) {
            $data = explode('|', $queue_item);
			$item = get_content_data($data[0], $data[1]);
			$item['type'] = $data[0];
			array_push($item_list, $item);
		}
		return $item_list;

	}

    function set_queued($type, $title, $to_queue) {
        
        global $user, $db;
        if (!$user)
            return;

		// check if it's already queued
		$item = sprintf('%s:%s', $type, $title);
		$was_queued = is_queued($type, $title);

		// check if we don't need to update anything
		$ignore_1 = $to_queue && $was_queued; // + +
		$ignore_2 = !$to_queue && !$was_queued; // - -
		if ($ignore_1 || $ignore_2)
			return true;

		// add item to array or remove it from array
        $queue = get_queue();
		if ($to_queue)
			array_push($queue, $item);
		else
			unset($queue[array_search($item, $queue)]);

		// encode data
		$data = json_encode($queue);

		// update data in database
		$update_queue = $db->prepare('UPDATE users SET queue=:queue WHERE username=:username');
		$update_queue->bindValue(':queue', $data);
		$update_queue->bindValue(':username', $user['username']);
		return $update_queue->execute();

	}
    
    function get_paste($id) {
        $url = sprintf('https://pastebin.com/raw/%s', $id);
        return file_get_contents($url);
    }
	
    function output_page_header() {
    	$header_path = dirname(__FILE__) . '/html/header.html';
    	$header = file_get_contents($header_path);
    	echo $header;
    }
    
    function output_page_footer() {
    	$header_path = dirname(__FILE__) . '/html/footer.html';
    	$header = file_get_contents($header_path);
    	echo $header;
    }

	function get_movie_data($url) {
		global $db;
		$get_data = $db->prepare('SELECT * FROM movies WHERE url=:url');
    	$get_data->bindValue(':url', $url);
    	$get_data->execute();
   		return $get_data->fetch();
	}

	function get_similar_movies($url) {
		$movie = get_movie_data($url);
		$similar_str = $movie['similar'];
		if (is_null($similar_str))
			return array();
		else
			return json_decode($similar_str, true);
	}

	function authenticated_movie_links($data) {
   		$qualities = json_decode($data['qualities'], true);
    	foreach ($qualities as &$quality)
        	$quality['link'] = authenticate_cdn_url($quality['link']);
      	return $qualities;
	}

	function authenticate_cdn_url($url, $is_server_request = false) {

		// important vars
		$ip = $is_server_request ? get_server_ip() : $GLOBALS['ip'];
		$key = '04187e37-4014-48cf-95f4-d6e6ea6c5094';
		$base_url = 'https://cdn.bytesurf.io';

		// determine the path of the file (remove base url)
		if (strpos($url, 'jexflix') != false)
		    $path = str_replace('https://cdn.jexflix.com', '', $url);
		else
		 	$path = str_replace('https://cdn.bytesurf.io', '', $url);

		// Set the time of expiry to one day from now
		$expires = time() + (60 * 60 * 24); 

		// establish token data
		$hash_me = $key.$path.$expires.$ip;

		// hash data and generate token
		$token = md5($hash_me, true);
		$token = base64_encode($token);
		$token = strtr($token, '+/', '-_');
		$token = str_replace('=', '', $token);

		// generate new url (note: decode special chars)
		// http://php.net/manual/en/function.htmlspecialchars-decode.php
		$url = "{$base_url}{$path}?token={$token}&expires={$expires}&ip={$ip}";
		return htmlspecialchars_decode($url);

	}

    function get_furthest_episode_link($title, $type, $completed) {     
        $data = get_furthest_episode($title, $type, $completed);
        switch ($type) {
            case 'show':
                return sprintf('https://bytesurf.io/%s?t=%s&s=%s&e=%s', 'show.php', $title, $data['season'], $data['episode']);
                break;
            case 'anime':
                return sprintf('https://bytesurf.io/%s?t=%s&e=%s', 'anime.php', $title, $data['episode']);
            default:
                return 'Type unrecognized';
        }     
    }

    // returns the furthest episode that the user did/didn't complete (progress tracker record)
    function get_furthest_episode($title, $type, $completed = false) {         
        $data = array('season' => -1, 'episode' => -1);        
        foreach (get_progress_tracker_data($completed) as $item) {
            if ($item['title'] != $title || $item['type'] != $type)
                continue;
            if ($item['season'] >= $data['season'] && $item['episode'] >= $data['episode'])
                $data = $item;
        }     
        return $data;     
    }

    function log_login($username) {
        global $db, $ip;
        $log_login = $db->prepare('INSERT INTO logins (username, ip_address, timestamp) VALUES (:username, :ip_address, :timestamp)');
    	$log_login->bindValue(':username', $username);
    	$log_login->bindValue(':ip_address', $ip);
    	$log_login->bindValue(':timestamp', time());
    	return $log_login->execute();    
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

	function get_server_ip() {
		global $ip_server;
		if (!isset($ip_server)) {
			$data = ip_api('', 'query');
			$ip_server = $data['query'];
		}
		return $ip_server;
	}

	function get_ip_info() {
		global $ip, $ip_info;
		if (!isset($ip_info))
			$ip_info = ip_api($ip, 192511);
		return $ip_info;
	}

	// using http://ip-api.com/ pro version
	function ip_api($ip, $fields) {
		$access_key = 'IkI7kEr9x3P51qu'; // NOTE TO SELF: GET NEW API KEY
		$url = sprintf('http://pro.ip-api.com/json/%s?key=%s&fields=%s', $ip, $access_key, $fields);
		$json = file_get_contents($url);
		$data = json_decode($json, true);
		return $data;
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

    function get_content_data($type, $url) {
    	$func = get_content_data_function($type);
    	$data = call_user_func($func, $url);
    	return $data;
    }

    function get_content_data_function($type) {
    	switch ($type) {
    		case 'movie':
    			return 'get_movie_data';
    		case 'anime':
    			return 'get_anime_data';
    		case 'show':
    			return 'get_series_data';
    	}
	}
	
	function delete_progress_entry() {
		global $db;
		$remove_data = $db->prepare('DELETE FROM progress_tracker WHERE title=:title AND type=:type AND episode=:episode AND season=:season');
		bind_content_values($remove_data);
		$remove_data->execute();
	}

	// Taken from updater.php
	function save_progress_entry($time, $time_total, $completed) {
        global $user, $db;
        if (get_progress($user['username']))
            $query = 'UPDATE progress_tracker SET time=:time, time_total=:time_total, completed=:completed WHERE username=:username AND title=:title AND type=:type AND season=:season AND episode=:episode';
        else
            $query = 'INSERT INTO progress_tracker (username, type, title, season, episode, time, time_total, completed) VALUES (:username, :type, :title, :season, :episode, :time, :time_total, :completed)';
        $save_progress = $db->prepare($query);
        bind_content_values($save_progress);
        $save_progress->bindValue(':username', $user['username']);
        $save_progress->bindValue(':time', $time);
        $save_progress->bindValue(':time_total', $time_total);
        $save_progress->bindValue(':completed', $completed);
        return $save_progress->execute();
	}
    
    // works like time(), but returns timestamp in milliseconds
    function time_ms() {
        return round(microtime(true) * 1000);
    }

    // exits script with gz compressed text
    function die_gz($txt) {
        // docs: https://www.php.net/manual/en/function.gzcompress.php
        // note: 9 = highest level of compression
        die(gzcompress($txt, 9));
    }

?>