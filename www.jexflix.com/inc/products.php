<?php
	
	// the # of seconds in a day (used for payment plans)
	define('SECONDS_PER_DAY', 86400);

	// format for plan initialization:
    // id number, short name, full name, price, duration
    init_plan(1, '1month', '1 Month Subscription', 8.99, SECONDS_PER_DAY * 30);
    init_plan(2, '3months', '3 Month Subscription', 19.99, SECONDS_PER_DAY * 90);
    init_plan(3, 'lifetime', 'Lifetime Subscription', 49.99, -1);

    // discount codes, % off
	global $discounts;
	$discounts = array(
		'penguware' => 10,
		'weebware' => 10
	);

	// return discount if needed
	if (isset($_GET['discount'])) {
		if (array_key_exists($_GET['discount'], $discounts))
			die(strval($discounts[$_GET['discount']]));
		else
			die('0');
	}

    function init_plan($id, $name_short, $name, $price, $duration) {
		global $product_ids, $products;
		$product_ids[$name_short] = $id;
		$product['name'] = $name;
		$product['price'] = $price;
		$product['duration'] = $duration;
		$products[$id] = $product;
	}

?>