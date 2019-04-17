<?php
    
    require '../inc/server.php';
    require '../inc/session.php';
    require_login();

    // # of videos shown on each page
	define('VIDEOS_PER_PAGE', 24);

	$get_movie_count = $db->prepare('SELECT * FROM movies');
	$get_movie_count->execute();
	$movie_count = $get_movie_count->rowCount();

    // all variables and their default values
    $GLOBALS['page'] = 1; // page #
    $vars = array(
    	'genre' => "Any", 
    	'quality' => 1080, 
    	'imdb_min' => 0.1,
    	'imdb_max' => 10.0, 
    	'year_min' => 2000,
    	'year_max' => 2019,
    	'query' => '' // search query ($_GET['search'])
    );

    // vars that are checked via 'LIKE' selection in sql query
    $vars_containify = array(
    	'genre', 
    	'quality',
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

    // make modifications if user has no selected genre
    if ($genre == 'Any') {
        $vars['genre'] = '%';
        unset($vars_containify[0]);
    }

    // update page # if it's set by the user
    if (isset($_GET['page']))
    	$GLOBALS['page'] = intval($_GET['page']);

    // get movies and determine if any videos were found correctly
    $movies = get_movies($vars, $page);
    if ($page < 1 || count($movies) == 0) // PAGE NUMBER IS INVALID or NO MOVIES ARE FOUND
        msg('Oh no :(', 'We couldn\'t find any movies fitting your request.');
        
    function get_movies($vars, $page) {

	   	global $db;


    	$get_movies = $db->prepare('SELECT * FROM `movies` WHERE LOWER(genres) LIKE :genre AND `qualities` LIKE :quality AND `rating` >= :imdb_min AND `rating` <= :imdb_max AND `year` >= :year_min AND `year` <= :year_max ORDER BY id DESC LIMIT :offset, :count');
    	foreach ($vars as $var => $default)
			$get_movies->bindValue(':' . $var, $default);
			
    	$get_movies_count = $db->prepare('SELECT * FROM `movies` WHERE LOWER(genres) LIKE :genre AND `qualities` LIKE :quality AND `rating` >= :imdb_min AND `rating` <= :imdb_max AND `year` >= :year_min AND `year` <= :year_max');
    	foreach ($vars as $var => $default)
    		$get_movies_count->bindValue(':' . $var, $default);


		$movie_offset = ($page - 1) * VIDEOS_PER_PAGE;
		$get_movies->bindValue(':offset', $movie_offset, PDO::PARAM_INT);
		$get_movies->bindValue(':count', VIDEOS_PER_PAGE, PDO::PARAM_INT);
	
		$get_movies->execute();
		$get_movies_count->execute();
	
		global $pagecount;
		$pagecount = $get_movies_count->rowCount() / VIDEOS_PER_PAGE;

    	$movies = $get_movies->fetchAll();
    	
    	return $movies;

	}
  	//echo $get_movies->rowCount() . PHP_EOL;
    function generate_page_url($new_page) {
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
	<meta name="author" content="Peter Pistachio">
	<title>ByteSurf</title>

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
	<? require '../inc/html/header.php' ?>
	<!-- end header -->


	<!-- page title -->
	<section class="section section--first section--bg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section__wrap">
						<!-- section title -->
						<h2 class="section__title">Movie Catalog</h2>
						<!-- end section title -->

						<!-- breadcrumb -->
						<ul class="breadcrumb">
							<li class="breadcrumb__item"><a href="../home">Home</a></li>
							<li class="breadcrumb__item breadcrumb__item--active">Movie Catalog</li>
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
									<input type="button" value="<?=$genre?>">
									<input type="hidden" name="genre" value="Any"/>
									<span></span>
								</div>
								<ul class="filter__item-menu dropdown-menu scrollbar-dropdown" aria-labelledby="filter-genre">
                                    <li>Any</li>
									<li>Action</li>
									<li>Adventure</li>
									<li>Animation</li>
									<li>Comedy</li>
									<li>Crime</li>
									<li>Documentary</li>
									<li>Drama</li>
									<li>Family</li>
									<li>Fantasy</li>
									<li>History</li>
									<li>Horror</li>
									<li>Music</li>
									<li>Mystery</li>
									<li>Romance</li>
									<li>Science-Fiction</li>
									<li>Thriller</li>
									<li>War</li>
									<li>Western</li>
								</ul>
							</div>
							<!-- end filter item -->

							<!-- filter item -->
							<div class="filter__item" id="filter__quality">
								<span class="filter__item-label">QUALITY:</span>

								<div class="filter__item-btn dropdown-toggle" role="navigation" id="filter-quality" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<input type="button" value="1080">
									<input type="hidden" name="quality" value="1080"/>
									<span></span>
								</div>

								<ul class="filter__item-menu dropdown-menu scrollbar-dropdown" aria-labelledby="filter-quality">
									<li>1080</li>
									<li>720</li>
								</ul>
							</div>
							<!-- end filter item -->

							<!-- filter item -->
							<div class="filter__item" id="filter__rate">
								<span class="filter__item-label">IMDB:</span>
								<div class="filter__item-btn dropdown-toggle" role="button" id="filter-rate" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<div class="filter__range">
										<div id="filter__imbd-start" contenteditable="true"></div>
										<div id="filter__imbd-end" contenteditable="true"></div>
										<input type="hidden" id="imdb_min" name="imdb_min">
										<input type="hidden" id="imdb_max" name="imdb_max">
									</div>
									<span></span>
								</div>

								<div class="filter__item-menu filter__item-menu--range dropdown-menu" aria-labelledby="filter-rate">
									<div id="filter__imbd"></div>
								</div>
							</div>
							<!-- end filter item -->

							<!-- filter item -->
							<div class="filter__item" id="filter__year">
								<span class="filter__item-label">RELEASE YEAR:</span>

								<div class="filter__item-btn dropdown-toggle" role="button" id="filter-year" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<div class="filter__range">
										<div id="filter__years-start"></div>
										<div id="filter__years-end"></div>
										<input type="hidden" id="year_min" name="year_min">
										<input type="hidden" id="year_max" name="year_max">
									</div>
									<span></span>
								</div>

								<div class="filter__item-menu filter__item-menu--range dropdown-menu" aria-labelledby="filter-year">
									<div id="filter__years"></div>
								</div>
							</div>
							<!-- end filter item -->
						</div>
						
						<!-- filter btn -->
						<button class="filter__btn" id ="catalog-submit" type="submit">apply filter</button>

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
				
				<?
					foreach ($movies as $movie) { 
						$url = 'https://bytesurf.io/movie.php?t=' . $movie['url'];
				?>   					
    			<div class="col-6 col-sm-4 col-lg-3 col-xl-2">
					<div class="card">
						<div class="card__cover">
							<img src=<?= authenticate_cdn_url($movie['thumbnail']) ?> alt="">
							<a href=<?= '"' . $url . '"' ?> class="card__play">
								<i class="icon ion-ios-play"></i>
							</a>
						</div>
						<div class="card__content">
							<h3 class="card__title"><a href=<?= '"' . $url . '"' ?>><?= $movie['title'] ?></a></h3>
							<span class="card__category">
								<a href=<?= '"https://jexflix.com/catalog/?year_min=' . $movie['year'] . '&year_max=' . $movie['year'] . '"' ?>>Released: <?= $movie['year'] 	?></a>
							</span>
							<span class="card__rate"><i class="icon ion-ios-star"></i><?= $movie['rating'] ?></span>
						</div>
					</div>
				</div>
				<? } ?>

				<!-- paginator -->
				<div class="col-12">
					<ul class="paginator" style="width: auto; box-shadow: 0 0 20px 0 rgba(0,0,0,0);">

						<?
						    $page = 1;
						    if (isset($_GET['page'])) $page = $_GET['page'];
						?>

						<? if ($page > 1) { ?><li class="paginator__item paginator__item--next"><a href="<?=generate_page_url($page - 1)?>"><i class="icon ion-ios-arrow-back"></i></a></li> <? } ?>
						<? if ($page > 2) { ?><li class="paginator__item"><a href="<?=generate_page_url(intval(1))?>"><?=intval(1)?></a></li> <? } ?>
						<? if ($page > 2) { ?> <li class="paginator__item"><a href="#">...</a></li> <? } ?>
						<? if ($page > 1) { ?><li class="paginator__item"><a href="<?=generate_page_url($page - 1)?>"><?=$page - 1?></a></li> <? } ?>
						<li class="paginator__item paginator__item--active"><a href="#"><?= $page ?></a></li>
						<? if ($page < $pagecount) { ?> <li class="paginator__item"><a href="<?=generate_page_url($page + 1)?>"><?=$page + 1?></a></li> <? } ?>
						<? if ($page < $pagecount) { ?> <li class="paginator__item"><a href="#">...</a></li> <? } ?>
						<? if ($page < $pagecount) { ?> <li class="paginator__item"><a href="<?=generate_page_url(intval($pagecount))?>"><?=intval($pagecount)?></a></li> <? } ?>
						<? if ($page < intval($pagecount + 1)) { ?><li class="paginator__item paginator__item--prev"><a href="<?=generate_page_url($page + 1)?>"><i class="icon ion-ios-arrow-forward"></i></a></li> <? } ?>

					</ul>
				</div>
				<!-- end paginator -->

			</div>
		</div>
	</div>
	<!-- end catalog -->

	
	<!-- footer -->
	<? require '../inc/html/footer.php' ?>
	<!-- end footer -->

</body>
</html>