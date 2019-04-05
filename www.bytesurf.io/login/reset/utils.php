<?php

	function get_password_reset($code) {
		global $db;
		$get = $db->prepare('SELECT * FROM password_resets WHERE code = :code');
		$get->bindValue(':code', $code);
		$get->execute();
		return $get->fetch();
	}

	function update_password_reset_status($code, $status) {
		global $db;
		$update = $db->prepare('UPDATE password_resets SET status = :status WHERE code = :code');
		$update->bindValue(':status', $status);
		$update->bindValue(':code', $code);
		return $update->execute();
	}

	function is_password_reset_expired($reset) {
		$limit = time() - 900; // 15 minutes ago
		return $reset['timestamp'] < $limit;
	}

?>