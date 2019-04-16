<?php

require '../inc/server.php';
require '../inc/session.php';
require_login();

// # of videos shown on each page
define('VIDEOS_PER_PAGE', 24);

// all variables and their default values
$GLOBALS['page'] = 1; // page #
$vars = array(
	'genre' => "Any",
	'rating_min' => 0.1,
	'rating_max' => 10.0,
	'query' => ''
);

// vars that are checked via 'LIKE' selection in sql query
$vars_containify = array(
	'genre',
	'query'
);

// set search query, or remove from array
if (isset($_GET['search']))
	$vars['query'] = $_GET['search'];
else
	unset($vars['query']);

// set $genre for use later in html (must be done before containify)
$genre = isset($_GET['genre']) ? $_GET['genre'] : $vars['genre'];

// set all filtering variables from $_GET and modify accordingly
foreach ($vars as $var => $default) {

	if (isset($_GET[$var]))
		$vars[$var] = $_GET[$var];

	if (in_array($var, $vars_containify))
		$vars[$var] = '%' . strtolower($vars[$var]) . '%';
}

// multiply ratings by 10 to account for 100 based rating system
$vars['rating_min'] *= 10;
$vars['rating_max'] *= 10;

// make modifications if user has no selected genre
if ($genre == 'Any') {
	$vars['genre'] = '%';
	unset($vars_containify[0]);
}

// update page # if it's set by the user
if (isset($_GET['page']))
	$GLOBALS['page'] = intval($_GET['page']);

// get animes and determine if any videos were found correctly
$animes = get_animes($vars, $page);

if ($page < 1 || count($animes) == 0) // PAGE NUMBER IS INVALID or NO MOVIES ARE FOUND
	msg('Oh no :(', 'We couldn\'t find any anime fitting your request.');

function get_animes($vars, $page)
{

	global $db;

	if (isset($vars['query'])) {
		$get_animes = $db->prepare('SELECT * FROM `anime` WHERE LOWER(similar) LIKE :query ORDER BY id DESC LIMIT :offset, :count');
		$get_animes->bindValue(':query', $vars['query']);
	} else if (isset($vars['genre'])) {
		$get_animes = $db->prepare('SELECT * FROM `anime` WHERE LOWER(genres) LIKE :genre AND `rating` >= :rating_min AND `rating` <= :rating_max ORDER BY id DESC LIMIT :offset, :count');
		foreach ($vars as $var => $default)
			$get_animes->bindValue(':' . $var, $default);
	}

	$anime_offset = ($page - 1) * VIDEOS_PER_PAGE;
	$get_animes->bindValue(':offset', $anime_offset, PDO::PARAM_INT);
	$get_animes->bindValue(':count', VIDEOS_PER_PAGE, PDO::PARAM_INT);

	$get_animes->execute();
	$animes = $get_animes->fetchAll();
	return $animes;
}

function generate_page_url($new_page)
{
	global $current_url, $page;
	$page_str = 'page=' . $page;
	$page_str_new = 'page=' . $new_page;
	if (contains($page_str, $current_url))
		return str_replace($page_str, $page_str_new, $current_url);
	else if (count($_GET) == 0)
		return $current_url . '?' . $page_str_new;
	else
		return $current_url . '&' . $page_str_new;
}

function check_if_watched($anime, $episode)
{
	global $db;
	// $get_watched = $db->prepare();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- Font -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600%7CUbuntu:300,400,500,700" rel="stylesheet">
	<!-- CSS -->
	<link rel="stylesheet" href="../css/bootstrap-reboot.min.css">
	<link rel="stylesheet" href="../css/bootstrap-grid.min.css">
	<link rel="stylesheet" href="../css/owl.carousel.min.css">
	<link rel="stylesheet" href="../css/jquery.mCustomScrollbar.min.css">
	<link rel="stylesheet" href="../css/nouislider.min.css">
	<link rel="stylesheet" href="../css/ionicons.min.css">
	<link rel="stylesheet" href="../css/plyr.css">
	<link rel="stylesheet" href="../css/photoswipe.css">
	<link rel="stylesheet" href="../css/default-skin.css">
	<link rel="stylesheet" href="../css/main.css">
	<!-- Favicons -->
	<link rel="icon" type="image/png" href="../icon/favicon-32x32.png" sizes="32x32">
	<link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
	<meta name="description" content="">
	<meta name="keywords" content="">A
	<meta name="author" content="Anthony Almond">
	<title>Bytesurf</title>
	<!-- JS -->
	<script src="../js/jquery-3.3.1.min.js"></script>
	<script src="../js/bootstrap.bundle.min.js"></script>
	<script src="../js/owl.carousel.min.js"></script>
	<script src="../js/jquery.mousewheel.min.js"></script>
	<script src="../js/jquery.mCustomScrollbar.min.js"></script>
	<script src="../js/wNumb.js"></script>
	<script src="../js/nouislider.min.js"></script>
	<script src="../js/plyr.min.js"></script>
	<script src="../js/jquery.morelines.min.js"></script>
	<script src="../js/photoswipe.min.js"></script>
	<script src="../js/photoswipe-ui-default.min.js"></script>
	<script src="../js/main.js"></script>
</head>

<body class="body">
	<!-- header -->
	<?= require '../inc/html/header.php' ?>
	<!-- end header -->

	<!-- page title -->
	<section class="section section--first section--bg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section__wrap">
						<!-- section title -->
						<h2 class="section__title">Anime Catalog</h2>
						<!-- end section title -->
						<!-- breadcrumb -->
						<ul class="breadcrumb">
							<li class="breadcrumb__item"><a href="../home">Home</a></li>
							<li class="breadcrumb__item breadcrumb__item--active">Anime Catalog</li>
						</ul>
						<!-- end breadcrumb -->
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- end page title -->
	<!-- filter -->
	<div class="filter">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<!-- form method get -->
					<form action="" method="get">
						<div class="filter__content">
							<div class="filter__items">
								<!-- filter item -->
								<div class="filter__item" id="filter__genre">
									<span class="filter__item-label">GENRE:</span>
									<div class="filter__item-btn dropdown-toggle" role="navigation" id="filter-genre" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<input type="button" value="<?php echo $genre ?>">
										<input type="hidden" name="genre" value="Any" />
										<span></span>
									</div>
									<ul class="filter__item-menu dropdown-menu scrollbar-dropdown" aria-labelledby="filter-genre">
										<li>Any</li>
										<li>Comedy</li>
										<li>Fantasy</li>
										<li>Romance</li>
										<li>Action</li>
										<li>School Life</li>
										<li>Drama</li>
										<li>Adventure</li>
										<li>Shoujo Ai</li>
										<li>Slice of Life</li>
										<li>Science Fiction</li>
										<li>Yaoi</li>
										<li>Sports</li>
										<li>Japan</li>
										<li>Earth</li>
										<li>Thriller</li>
										<li>Historical</li>
										<li>Present</li>
										<li>Mystery</li>
										<li>Harem</li>
										<li>Asia</li>
										<li>Magic</li>
										<li>Kids</li>
										<li>Horror</li>
										<li>Mecha</li>
										<li>Music</li>
										<li>Psychological</li>
										<li>Super Power</li>
										<li>Shounen Ai</li>
										<li>Martial Arts</li>
										<li>Demon</li>
										<li>Military</li>
										<li>Plot Continuity</li>
										<li>Fantasy World</li>
										<li>Motorsport</li>
										<li>Violence</li>
										<li>Parody</li>
										<li>Space</li>
										<li>Future</li>
										<li>Contemporary Fantasy</li>
										<li>Past</li>
									</ul>
								</div>
								<!-- end filter item -->

								<!-- filter item -->
								<div class="filter__item" id="filter__rate">
									<span class="filter__item-label">RATING:</span>
									<div class="filter__item-btn dropdown-toggle" role="button" id="filter-rate" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<div class="filter__range">
											<div id="filter__imbd-start" contenteditable="true"></div>
											<div id="filter__imbd-end" contenteditable="true"></div>
											<input type="hidden" id="imdb_min" name="rating_min">
											<input type="hidden" id="imdb_max" name="rating_max">
										</div>
										<span></span>
									</div>
									<div class="filter__item-menu filter__item-menu--range dropdown-menu" aria-labelledby="filter-rate">
										<div id="filter__imbd"></div>
									</div>
								</div>
								<!-- end filter item -->
							</div>
							<!-- filter btn -->
							<button class="filter__btn" id="catalog-submit" type="submit">Add to Watched</button>
							<!-- end filter btn -->
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- end filter -->
	<!-- catalog -->
	<div class="catalog">
		<div class="container">
			<div class="row">
				<?php
				foreach ($animes as $anime) {
					$url = 'https://bytesurf.io/anime.php?t=' . $anime['url'];
					?>
					<div class="col-6 col-sm-4 col-lg-3 col-xl-2">
						<div class="card">
							<div class="card__cover">
								<img src="<?php echo authenticate_cdn_url($anime['thumbnail']) ?>" alt="" style="width: 100%; height: 255px;">
								<a href=<?php echo '"' . $url . '"' ?> class="card__play">
									<i class="icon ion-ios-play"></i>
								</a>
							</div>
							<div class="card__content">
								<h3 class="card__title"><a href=<?php echo '"' . $url . '"' ?>><?php echo $anime['title'] ?></a></h3>
								<span class="card__category">
									<a>Released: <?php echo $anime['release_date'] ?></a>
								</span>
								<span class="card__rate"><i class="icon ion-ios-star"></i><?php echo round($anime['rating'] / 10, 1) ?></span>
							</div>
						</div>
					</div>
				<?php
			} ?>
				<!-- paginator -->
				<script>
					// updates the front/back urls

					function paginate(p1, p2) {
						var paginators = document.getElementsByClassName('paginator__item');
						var prev = document.getElementById("page-prev");
						var next = document.getElementById("page-next");
						prev.href = (paginators[p1]).children[0].href;
						next.href = (paginators[p2]).children[0].href;
					}
				</script>
				<div class="col-12">
					<ul class="paginator">
						<li class="paginator__item paginator__item--prev">
							<a id="page-prev" href="#"><i class="icon ion-ios-arrow-back"></i></a>
						</li>
						<?php
						$is_first_page = $page == 1;
						$is_last_page = count(get_animes($vars, $page + 1)) == 0;

						// it's probably the last page if the number of videos on the next page is 0
						// this can probably be done better but im keeping it this way until the backend code is done

						if ($is_first_page) { ?>
							<li class="paginator__item paginator__item--active"><a href="#"><?php echo $page?></a></li>
							<li class="paginator__item"><a href=<?php echo '"' . generate_page_url($page + 1) . '"' ?>><?php echo $page + 1 ?></a></li>
							<li class="paginator__item"><a href=<?php echo '"' . generate_page_url($page + 2) . '"' ?>><?php echo $page + 2 ?></a></li>
							<script>
								$(function() {
									paginate(1, 2)
								});
							</script>
						<?php
					} else if ($is_last_page) { ?>
							<li class="paginator__item"><a href=<?php echo '"' . generate_page_url($page - 2) . '"' ?>><?php echo $page - 2 ?></a></li>
							<li class="paginator__item"><a href=<?php echo '"' . generate_page_url($page - 1) . '"' ?>><?php echo $page - 1 ?></a></li>
							<li class="paginator__item paginator__item--active"><a href="#"><?php echo $page ?></a></li>
							<script>
								$(function() {
									paginate(2, 3)
								});
							</script>
						<?php
					} else { ?>
							<li class="paginator__item"><a href=<?php echo '"' . generate_page_url($page - 1) . '"' ?>><?php echo $page - 1 ?></a></li>
							<li class="paginator__item paginator__item--active"><a href="#"><?php echo $page ?></a></li>
							<li class="paginator__item"><a href=<?php echo '"' . generate_page_url($page + 1) . '"' ?>><?php echo $page + 1 ?></a></li>
							<script>
								$(function() {
									paginate(1, 3)
								});
							</script>
						<?php
					}
					?>
						<!--
						old stuff before making this functional
						<li class="paginator__item"><a href="#">1</a></li>
						<li class="paginator__item paginator__item--active"><a href="#">2</a></li>
						<li class="paginator__item"><a href="#">3</a></li>
						-->
						<li class="paginator__item paginator__item--next">
							<a id="page-next" href="#"><i class="icon ion-ios-arrow-forward"></i></a>
						</li>
					</ul>
				</div>
				<!-- end paginator -->
			</div>
		</div>
	</div>
	<!-- end catalog -->
	<!-- footer -->
	<?= require '../inc/html/footer.php' ?>
	<!-- end footer -->
</body>

</html>