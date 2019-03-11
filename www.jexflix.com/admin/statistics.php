<?php

	// gets total $ (USD) made from x days ago
	function get_total_days_ago($days) {
		return get_direct_sales_days_ago($days) + get_reseller_deposits_days_ago($days);
	}

	// gets amount of $ (USD) x days ago from direct sales
	function get_direct_sales_days_ago($days) {
		global $db;
		$get_sales = $db->prepare('SELECT SUM(amount_usd) FROM orders_btc WHERE timestamp>=:timestamp_min AND timestamp<:timestamp_max AND product != :not_product AND status = :status');
		apply_day_timestamps($get_sales, $days);
		$get_sales->bindValue(':not_product', 'Reseller Deposit');
		$get_sales->bindValue(':status', 'completed');
		return get_sum($get_sales);
	}

	// gets amount of $ (USD) x days ago from direct sales
	function get_reseller_deposits_days_ago($days) {
		global $db;
		$get_deposits = $db->prepare('SELECT SUM(amount_usd) FROM orders_btc WHERE timestamp>=:timestamp_min AND timestamp<:timestamp_max AND product = :is_product AND status = :status');
		apply_day_timestamps($get_deposits, $days);
		$get_deposits->bindValue(':is_product', 'Reseller Deposit');
		$get_deposits->bindValue(':status', 'completed');
		return get_sum($get_deposits);
	}

	function get_reseller_sales($days) {
		global $db;
		$get_sales = $db->prepare('SELECT SUM(amount) FROM orders_pp WHERE timestamp>=:timestamp_min AND timestamp<:timestamp_max AND status = :status');
		apply_day_timestamps($get_sales, $days);
		$get_sales->bindValue(':status', 'completed');
		return get_sum($get_sales);
	}

	function get_registrations($days) {
		global $db;
		$get_registrations = $db->prepare('SELECT * FROM registrations WHERE timestamp>=:timestamp_min AND timestamp<:timestamp_max');
		apply_day_timestamps($get_registrations, $days);
		$get_registrations->execute();
		return $get_registrations->rowCount();
	}

	function get_logins($days) {
		global $db;
		$get_logins = $db->prepare('SELECT * FROM logins WHERE timestamp>=:timestamp_min AND timestamp<:timestamp_max');
		apply_day_timestamps($get_logins, $days);
		$get_logins->execute();
		return $get_logins->rowCount();
	}

	// === FUNCTIONS USED FOR A BUNCH OF STUFF ===

	// get the sum from a prepared SELECT SUM() query (includes execution)
	function get_sum($stmt) {
		$stmt->execute();
		$data = $stmt->fetch();
		$amount = current($data);
		return floatval($amount);
	}

	// aplies timestamp_min and timestamp_max to a prepared statement
	function apply_day_timestamps($stmt, $days) {

		// get min/max timestamps to filter by
		$timestamp_min = timestamp_days_ago($days);
		$timestamp_max = timestamp_days_ago($days - 1);

		// apply to statement
		$stmt->bindValue(':timestamp_min', $timestamp_min);
		$stmt->bindValue(':timestamp_max', $timestamp_max);

	}

	// returns month/day x days ago (ex: "January 1st" would be returned on January 2nd if $days == 1)
	function day_desc($days) {
		return date('F jS', timestamp_days_ago($days));
	}

	// returns a timestamp x number of days ago
	function timestamp_days_ago($days) {
		date_default_timezone_set('UTC');
		$timestamp = strtotime('-' . $days . ' day'); // get the timestamp exactly 3 days ago
		$date_str = date("Y-m-d", $timestamp); // convert it to just the day
		$timestamp = strtotime($date_str); // get the timestamp again, aka that day at 12 am
		return $timestamp;
	}

	function output_data($func, $days, $stringify = false) {

		for ($i = $days; $i >= 0; $i--) {

			$value = call_user_func($func, $i);

			if ($stringify)
				$value = "'" . $value . "'"; // make it a string, add quotes
			else
				$value = round($value, 2); // its a numeric value, round to 2 decimal places

			$value .= ',';

			echo $value;

		}

	}

?>