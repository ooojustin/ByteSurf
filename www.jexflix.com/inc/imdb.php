<?php

	function get_imdb_similar_titles($url, $convert_to_movie_urls = false) {
		$imdb_data = get_imdb_page_data($url);
		$regex = '/<a href="\/title\/.*?\/\?ref_=tt_rec_tti"\s><img height="113" width="76" alt=".*?" title=".*?" src=".*?" class="loadlate hidden rec_poster_img" loadlate=".*?" \/>/m';
		if (preg_match_all($regex, $imdb_data, $matches) === FALSE)
			return array();
		$titles = array();
		$matches = current($matches);
		foreach ($matches as $match) {
			$href = explode('"', $match)[1];
			$title = explode('/', $href)[2];
			array_push($titles, $title);
		}
		if ($convert_to_movie_urls) {
			// todo
		} else return $titles;
	}

	function get_imdb_rating($url) {
		$imdb_data = get_imdb_page_data($url);
		$regex = '/<span class="rating">.*<span class="ofTen">\/10<\/span><\/span>/m';
		if (!preg_match($regex, $imdb_data, $matches))
			return doubleval(-1);
		// forgive me lord for i have sinned:
		return doubleval(explode('<', explode('>', $matches[0])[1])[0]);
	}

	function get_imdb_page_data($url) {
		$movie_data = get_movie_data($url);
		$imdb_url = 'https://www.imdb.com/title/' . $movie_data['imdb_id'];
		$imdb_raw = file_get_contents($imdb_url);
		return $imdb_raw;
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

?>