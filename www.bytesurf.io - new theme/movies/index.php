<?php
    
	include '../inc/server.php';
	include '../inc/session.php';
	
    // # of videos shown on each page
	define('VIDEOS_PER_PAGE', 16);
	
	$get_movie_count = $db->prepare('SELECT * FROM movies');
	$get_movie_count->execute();
	$movie_count = $get_movie_count->rowCount();
	
    // all variables and their default values
    $GLOBALS['page'] = 1; // page #
    $vars = array(
    	'genre' => "Action", 
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

    // update page # if it's set by the user
    if (isset($_GET['page']))
    	$GLOBALS['page'] = intval($_GET['page']);

    // get movies and determine if any videos were found correctly
    $movies = get_movies($vars, $page);
    if ($page < 1 || count($movies) == 0) {
    	// PAGE NUMBER IS INVALID or NO MOVIES ARE FOUND
		// somebody handle this with something (@trevor)
		//header("location: https://jexflix.com/404?e=video");		
    	die('No videos found.');
    }
    

    function get_movies($vars, $page) {

	   	global $db;

        if (isset($vars['query']) && isset($vars['quality']) && isset($vars['imdb_min']) && isset($vars['imdb_max']) && isset($vars['year_min']) && isset($vars['year_max']) && isset($vars['query'])) {
    		$get_movies = $db->prepare('SELECT * FROM `movies` WHERE LOWER(genres) LIKE :genre AND `qualities` LIKE :quality AND `rating` >= :imdb_min AND `rating` <= :imdb_max AND `year` >= :year_min AND `year` <= :year_max AND LOWER(title) LIKE :query ORDER BY id DESC LIMIT :offset, :count');
    		foreach ($vars as $var => $default)
    			$get_movies->bindValue(':' . $var, $default);
    			
    		$get_movies_count = $db->prepare('SELECT * FROM `movies` WHERE LOWER(genres) LIKE :genre AND `qualities` LIKE :quality AND `rating` >= :imdb_min AND `rating` <= :imdb_max AND `year` >= :year_min AND `year` <= :year_max AND LOWER(title) LIKE :query');
    		foreach ($vars as $var => $default)
    			$get_movies_count->bindValue(':' . $var, $default);
        }
	   	else if (isset($vars['query'])) {
	   		$get_movies = $db->prepare('SELECT * FROM `movies` WHERE LOWER(title) LIKE :query ORDER BY id DESC LIMIT :offset, :count');
	   		$get_movies_count = $db->prepare('SELECT * FROM `movies` WHERE LOWER(title) LIKE :query');
	   		$get_movies->bindValue(':query', $vars['query']);
	   		$get_movies_count->bindValue(':query', $vars['query']);
	   	} else {
    		$get_movies = $db->prepare('SELECT * FROM `movies` WHERE LOWER(genres) LIKE :genre AND `qualities` LIKE :quality AND `rating` >= :imdb_min AND `rating` <= :imdb_max AND `year` >= :year_min AND `year` <= :year_max ORDER BY id DESC LIMIT :offset, :count');
    		foreach ($vars as $var => $default)
    			$get_movies->bindValue(':' . $var, $default);
    			
    		$get_movies_count = $db->prepare('SELECT * FROM `movies` WHERE LOWER(genres) LIKE :genre AND `qualities` LIKE :quality AND `rating` >= :imdb_min AND `rating` <= :imdb_max AND `year` >= :year_min AND `year` <= :year_max');
    		foreach ($vars as $var => $default)
    			$get_movies_count->bindValue(':' . $var, $default);
    	}

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
<!--[if IE 7]>
<html class="ie ie7 no-js" lang="en-US">
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8 no-js" lang="en-US">
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html lang="en" class="no-js">
<head>
	<!-- Basic need -->
	<title>ByteSurf</title>
	<meta charset="UTF-8">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<link rel="profile" href="#">

    <!--Google Font-->
    <link rel="stylesheet" href='http://fonts.googleapis.com/css?family=Dosis:400,700,500|Nunito:300,400,600' />
	<!-- Mobile specific meta -->
	<meta name=viewport content="width=device-width, initial-scale=1">
	<meta name="format-detection" content="telephone-no">

	<!-- CSS files -->
	<link rel="stylesheet" href="../css/plugins.css">
	<link rel="stylesheet" href="../css/style.css">

</head>
<body>
<!--preloading-->
<div id="preloader">
    <img class="logo" src="../images/logo1.png" alt="" width="119" height="58">
    <div id="status">
        <span></span>
        <span></span>
    </div>
</div>
<!--end of preloading-->

<!-- BEGIN | Header -->
<?=output_page_header();?>
<!-- END | Header -->

<div class="hero common-hero">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="hero-ct">
					<h1> movie listing - grid</h1>
					<ul class="breadcumb">
						<li class="active"><a href="#">Home</a></li>
						<li> <span class="ion-ios-arrow-right"></span> movie listing</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="page-single">
	<div class="container">
		<div class="row ipad-width">
			<div class="col-md-8 col-sm-12 col-xs-12">
				<div class="topbar-filter">
					<p>Found <span><?=number_format($movie_count)?> movies in total</span></p>
					<div class="pagination2">
					    
						<?
						    $page = 1;
						    if (isset($_GET['page'])) $page = $_GET['page'];
						?>
						
						<span>Page <?=$page?> of <?=intval($pagecount) + 1?>:</span>
						<? if ($page > 1) { ?><a href="<?=generate_page_url($page - 1)?>"><i class="ion-arrow-left-b"></i></a> <? } ?>
						<? if ($page > 2) { ?> <a href="<?=generate_page_url(intval(1))?>"><?=intval(1)?></a> <? } ?>
						<? if ($page > 2) { ?> <a href="#">...</a> <? } ?>
						<? if ($page > 1) { ?><a href="<?=generate_page_url($page - 1)?>"><?=$page - 1?></a> <? } ?>
						<a class="active" href="#"><?=$page?></a>
						<? if ($page < $pagecount) { ?> <a href="<?=generate_page_url($page + 1)?>"><?=$page + 1?></a> <? } ?>
						<? if ($page < $pagecount) { ?> <a href="#">...</a> <? } ?>
						<? if ($page < $pagecount) { ?> <a href="<?=generate_page_url(intval($pagecount) + 1)?>"><?=intval($pagecount) + 1?></a> <? } ?>
						<? if ($page < intval($pagecount + 1)) { ?><a href="<?=generate_page_url($page + 1)?>"><i class="ion-arrow-right-b"></i></a> <? } ?>
						
						<? ?>
					</div>
				</div>
				<div class="flex-wrap-movielist">
				    
				    
				    <?
				        foreach ($movies as $movie) {
				            $url = 'https://bytesurf.io/movie.php?t=' . $movie['url'];
				    
				    ?>
				    
						<div class="movie-item-style-2 movie-item-style-1">
							<a href="<?=$url?>"><img src="<?= str_replace('cdn.jexflix.com', 'jexflix.b-cdn.net', authenticate_cdn_url($movie['thumbnail'])) ?>" alt=""></a>
							<div class="mv-item-infor">
								<h6><a href="<?=$url?>"><?=$movie['title']?></a></h6>
								<p class="rate"><i class="ion-android-star"></i><span><?=$movie['rating']?></span> /10</p>
							</div>
						</div>	
						
						
					<? } ?>
					
					

				</div>		
				<div class="topbar-filter">
					<select>
						<option value="range">16 Movies</option>
						<option value="range">24 Movies</option>
						<option value="range">48 Movies</option>
					</select>
					
					<div class="pagination2">
					    
						<?
						    $page = 1;
						    if (isset($_GET['page'])) $page = $_GET['page'];
						?>
						
						<span>Page <?=$page?> of <?=intval($pagecount) + 1?>:</span>
						<? if ($page > 1) { ?><a href="<?=generate_page_url($page - 1)?>"><i class="ion-arrow-left-b"></i></a> <? } ?>
						<? if ($page > 2) { ?> <a href="<?=generate_page_url(intval(1))?>"><?=intval(1)?></a> <? } ?>
						<? if ($page > 2) { ?> <a href="#">...</a> <? } ?>
						<? if ($page > 1) { ?><a href="<?=generate_page_url($page - 1)?>"><?=$page - 1?></a> <? } ?>
						<a class="active" href="#"><?=$page?></a>
						<? if ($page < $pagecount) { ?> <a href="<?=generate_page_url($page + 1)?>"><?=$page + 1?></a> <? } ?>
						<? if ($page < $pagecount) { ?> <a href="#">...</a> <? } ?>
						<? if ($page < $pagecount) { ?> <a href="<?=generate_page_url(intval($pagecount) + 1)?>"><?=intval($pagecount) + 1?></a> <? } ?>
						<? if ($page < intval($pagecount + 1)) { ?><a href="<?=generate_page_url($page + 1)?>"><i class="ion-arrow-right-b"></i></a> <? } ?>
						
						<? ?>
					</div>
				</div>
			</div>
			<div class="col-md-4 col-sm-12 col-xs-12">
				<div class="sidebar">
					<div class="searh-form">
						<h4 class="sb-title">Search for movie</h4>
						<form class="form-style-1" action="" method="get">
							<div class="row">
								<div class="col-md-12 form-it">
									<label>Movie name</label>
									<input type="text" name="search" placeholder="Enter keywords">
								</div>
								<div class="col-md-12 form-it">
									<label>Genres & Subgenres</label>
									<div class="group-ip">
										<select
											name="skills" multiple="" class="ui fluid dropdown">
											<option value="">Enter to filter genres</option>
											<option value="Action1">Action 1</option>
					                        <option value="Action2">Action 2</option>
					                        <option value="Action3">Action 3</option>
					                        <option value="Action4">Action 4</option>
					                        <option value="Action5">Action 5</option>
										</select>
									</div>	
								</div>
								<div class="col-md-12 form-it">
									<label>Rating (From - To)</label>
									<div class="row">
										<div class="col-md-6">
											<input type="number" min="0" name="imdb_min" value="0">
										</div>
										<div class="col-md-6">
											<input type="number" name="imdb_max" max="10" value="10">
										</div>
									</div>
								</div>
								<div class="col-md-12 form-it">
									<label>Release Year (From - To)</label>
									<div class="row">
										<div class="col-md-6">
											<input type="number" min="1900" name="year_min" value="1900">
										</div>
										<div class="col-md-6">
											<input type="number" name="year_max" max="2019" value="2019">
										</div>
									</div>
								</div>
								<div class="col-md-12 ">
									<input class="submit" type="submit" value="submit">
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- footer section-->
<?= output_page_footer(); ?>
<!-- end of footer section-->

<script src="../js/jquery.js"></script>
<script src="../js/plugins.js"></script>
<script src="../js/plugins2.js"></script>
<script src="../js/custom.js"></script>
</body>
</html>