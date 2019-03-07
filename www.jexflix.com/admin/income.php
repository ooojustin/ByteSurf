<?php
	
	// returns month/day x days ago (ex: "January 1st" would be returned on January 2nd if $days == 1)
	function day_desc($days) {
		return date('F jS', timestamp_days_ago($days));
	}

	function timestamp_days_ago($days) {
		date_default_timezone_set('UTC');
		$timestamp = strtotime('-' . $days . ' day'); // get the timestamp exactly 3 days ago
		$date_str = date("Y-m-d", $timestamp); // convert it to just the day
		$timestamp = strtotime($date_str); // get the timestamp again, aka that day at 12 am
		return $timestamp;
	}

	// gets total $ (USD) made from x days ago
	function get_total_days_ago($days) {
		return get_direct_sales_days_ago($days) + get_reseller_deposits_days_ago($days);
	}

	// gets amount of $ (USD) x days ago from direct sales
	function get_direct_sales_days_ago($days) {

		global $db;

		// execute query
		$get_sales = $db->prepare('SELECT SUM(amount_usd) FROM orders_btc WHERE timestamp>=:timestamp_min AND timestamp<:timestamp_max AND product != :not_product');
		apply_day_timestamps($get_sales, $days);
		$get_sales->bindValue(':not_product', 'Reseller Deposit');
		$get_sales->execute();

		$data = $get_sales->fetch();
		$amount = current($data);

		return intval($amount);

	}

		// gets amount of $ (USD) x days ago from direct sales
	function get_reseller_deposits_days_ago($days) {

		global $db;

		// execute query
		$get_deposits = $db->prepare('SELECT SUM(amount_usd) FROM orders_btc WHERE timestamp>=:timestamp_min AND timestamp<:timestamp_max AND product = :is_product');
		apply_day_timestamps($get_deposits, $days);
		$get_deposits->bindValue(':is_product', 'Reseller Deposit');
		$get_deposits->execute();

		$data = $get_deposits->fetch();
		$amount = current($data);

		return intval($amount);

	}

	function apply_day_timestamps($stmt, $days) {

		// get min/max timestamps to filter by
		$timestamp_min = timestamp_days_ago($days);
		$timestamp_max = timestamp_days_ago($days - 1);

		// apply to statement
		$stmt->bindValue(':timestamp_min', $timestamp_min);
		$stmt->bindValue(':timestamp_max', $timestamp_max);

	}

?>