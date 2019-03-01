<?php
	
	// discount codes, % off
	$GLOBALS['discounts'] = array(
		'penguware' => 10,
		'weebware' => 10
	);

	// format for plan initialization:
    // id number, short name, full name, price, duration
    init_plan(1, '1month', '1 Month Subscription', 8.99, 86400);
    init_plan(2, '3months', '3 Month Subscription', 19.99, 259200);
    init_plan(3, 'lifetime', 'Lifetime Subscription', 49.99, -1);

    function init_plan($id, $name_short, $name, $price, $duration) {
		global $product_ids, $products;
		$product_ids[$name_short] = $id;
		$product['name'] = $name;
		$product['price'] = $price;
		$product['duration'] = $duration;
		$products[$id] = $product;
	}

?>