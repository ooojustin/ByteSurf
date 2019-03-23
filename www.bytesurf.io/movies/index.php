<?php
    
	include '../inc/server.php';
	include '../inc/session.php';
	
	$get_movie_count = $db->prepare('SELECT * FROM movies');
	$get_movie_count->execute();
	$movie_count = $get_movie_count->rowCount();
	
    // # of videos shown on each page
	define('VIDEOS_PER_PAGE', 16);

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
        }
	   	else if (isset($vars['query'])) {
	   		$get_movies = $db->prepare('SELECT * FROM `movies` WHERE LOWER(title) LIKE :query ORDER BY id DESC LIMIT :offset, :count');
	   		$get_movies->bindValue(':query', $vars['query']);
	   	} else {
    		$get_movies = $db->prepare('SELECT * FROM `movies` WHERE LOWER(genres) LIKE :genre AND `qualities` LIKE :quality AND `rating` >= :imdb_min AND `rating` <= :imdb_max AND `year` >= :year_min AND `year` <= :year_max ORDER BY id DESC LIMIT :offset, :count');
    		foreach ($vars as $var => $default)
    			$get_movies->bindValue(':' . $var, $default);
    	}

    	$movie_offset = ($page - 1) * VIDEOS_PER_PAGE;
    	$get_movies->bindValue(':offset', $movie_offset, PDO::PARAM_INT);
    	$get_movies->bindValue(':count', VIDEOS_PER_PAGE, PDO::PARAM_INT);

    	$get_movies->execute();
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
<header class="ht-header">
	<div class="container">
		<nav class="navbar navbar-default navbar-custom">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header logo">
				    <div class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					    <span class="sr-only">Toggle navigation</span>
					    <div id="nav-icon1">
							<span></span>
							<span></span>
							<span></span>
						</div>
				    </div>
				    <a href="index.html"><img class="logo" src="../images/logo1.png" alt="" width="119" height="58"></a>
			    </div>
				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse flex-parent" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav flex-child-menu menu-left">
						<li class="hidden">
							<a href="#page-top"></a>
						</li>
						<li class="dropdown first">
							<a class="btn btn-default dropdown-toggle lv1" data-toggle="dropdown">
							Home <i class="fa fa-angle-down" aria-hidden="true"></i>
							</a>
							<ul class="dropdown-menu level1">
								<li><a href="index.html">Home 01</a></li>
								<li><a href="homev2.html">Home 02</a></li>
								<li><a href="homev3.html">Home 03</a></li>
							</ul>
						</li>
						<li class="dropdown first">
							<a class="btn btn-default dropdown-toggle lv1" data-toggle="dropdown" data-hover="dropdown">
							movies<i class="fa fa-angle-down" aria-hidden="true"></i>
							</a>
							<ul class="dropdown-menu level1">
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown" >Movie grid<i class="ion-ios-arrow-forward"></i></a>
									<ul class="dropdown-menu level2">
										<li><a href="moviegrid.html">Movie grid</a></li>
										<li><a href="moviegridfw.html">movie grid full width</a></li>
									</ul>
								</li>			
								<li><a href="movielist.html">Movie list</a></li>
								<li><a href="moviesingle.html">Movie single</a></li>
								<li class="it-last"><a href="seriessingle.html">Series single</a></li>
							</ul>
						</li>
						<li class="dropdown first">
							<a class="btn btn-default dropdown-toggle lv1" data-toggle="dropdown" data-hover="dropdown">
							celebrities <i class="fa fa-angle-down" aria-hidden="true"></i>
							</a>
							<ul class="dropdown-menu level1">
								<li><a href="celebritygrid01.html">celebrity grid 01</a></li>
								<li><a href="celebritygrid02.html">celebrity grid 02 </a></li>
								<li><a href="celebritylist.html">celebrity list</a></li>
								<li class="it-last"><a href="celebritysingle.html">celebrity single</a></li>
							</ul>
						</li>
						<li class="dropdown first">
							<a class="btn btn-default dropdown-toggle lv1" data-toggle="dropdown" data-hover="dropdown">
							news <i class="fa fa-angle-down" aria-hidden="true"></i>
							</a>
							<ul class="dropdown-menu level1">
								<li><a href="bloglist.html">blog List</a></li>
								<li><a href="bloggrid.html">blog Grid</a></li>
								<li class="it-last"><a href="blogdetail.html">blog Detail</a></li>
							</ul>
						</li>
						<li class="dropdown first">
							<a class="btn btn-default dropdown-toggle lv1" data-toggle="dropdown" data-hover="dropdown">
							community <i class="fa fa-angle-down" aria-hidden="true"></i>
							</a>
							<ul class="dropdown-menu level1">
								<li><a href="userfavoritegrid.html">user favorite grid</a></li>
								<li><a href="userfavoritelist.html">user favorite list</a></li>
								<li><a href="userprofile.html">user profile</a></li>
								<li class="it-last"><a href="userrate.html">user rate</a></li>
							</ul>
						</li>
					</ul>
					<ul class="nav navbar-nav flex-child-menu menu-right">
						<li class="dropdown first">
							<a class="btn btn-default dropdown-toggle lv1" data-toggle="dropdown" data-hover="dropdown">
							pages <i class="fa fa-angle-down" aria-hidden="true"></i>
							</a>
							<ul class="dropdown-menu level1">
								<li><a href="landing.html">Landing</a></li>
								<li><a href="404.html">404 Page</a></li>
								<li class="it-last"><a href="comingsoon.html">Coming soon</a></li>
							</ul>
						</li>                
						<li><a href="#">Help</a></li>
						<li class="loginLink"><a href="#">LOG In</a></li>
						<li class="btn signupLink"><a href="#">sign up</a></li>
					</ul>
				</div>
			<!-- /.navbar-collapse -->
	    </nav>
	    
	    <!-- top search form -->
	    <div class="top-search">
	    	<select>
				<option value="united">TV show</option>
				<option value="saab">Others</option>
			</select>
			<input type="text" placeholder="Search for a movie, TV Show or celebrity that you are looking for">
	    </div>
	</div>
</header>
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
					<p>Found <span><?=number_format($movie_count)?> movies</span> in total</p>
					<label>Sort by:</label>
					<select>
						<option value="popularity">Popularity Descending</option>
						<option value="popularity">Popularity Ascending</option>
						<option value="rating">Rating Descending</option>
						<option value="rating">Rating Ascending</option>
						<option value="date">Release date Descending</option>
						<option value="date">Release date Ascending</option>
					</select>
					<a href="movielist.html" class="list"><i class="ion-ios-list-outline "></i></a>
					<a  href="moviegrid.html" class="grid"><i class="ion-grid active"></i></a>
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
					<label>Movies per page:</label>
					<select>
						<option value="range">16 Movies</option>
						<option value="range">24 Movies</option>
						<option value="range">48 Movies</option>
					</select>
					
					<div class="pagination2">
						<span>Page 1 of 2:</span>
						<a class="active" href="#">1</a>
						<a href="#">2</a>
						<a href="#">3</a>
						<a href="#">...</a>
						<a href="#">78</a>
						<a href="#">79</a>
						<a href="#"><i class="ion-arrow-right-b"></i></a>
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
<footer class="ht-footer">
	<div class="container">
		<div class="flex-parent-ft">
			<div class="flex-child-ft item1">
				 <a href="index.html"><img class="logo" src="../images/logo1.png" alt=""></a>
				 <p>5th Avenue st, manhattan<br>
				New York, NY 10001</p>
				<p>Call us: <a href="#">(+01) 202 342 6789</a></p>
			</div>
			<div class="flex-child-ft item2">
				<h4>Resources</h4>
				<ul>
					<li><a href="#">About</a></li> 
					<li><a href="#">Blockbuster</a></li>
					<li><a href="#">Contact Us</a></li>
					<li><a href="#">Forums</a></li>
					<li><a href="#">Blog</a></li>
					<li><a href="#">Help Center</a></li>
				</ul>
			</div>
			<div class="flex-child-ft item3">
				<h4>Legal</h4>
				<ul>
					<li><a href="#">Terms of Use</a></li> 
					<li><a href="#">Privacy Policy</a></li>	
					<li><a href="#">Security</a></li>
				</ul>
			</div>
			<div class="flex-child-ft item4">
				<h4>Account</h4>
				<ul>
					<li><a href="#">My Account</a></li> 
					<li><a href="#">Watchlist</a></li>	
					<li><a href="#">Collections</a></li>
					<li><a href="#">User Guide</a></li>
				</ul>
			</div>
			<div class="flex-child-ft item5">
				<h4>Newsletter</h4>
				<p>Subscribe to our newsletter system now <br> to get latest news from us.</p>
				<form action="#">
					<input type="text" placeholder="Enter your email...">
				</form>
				<a href="#" class="btn">Subscribe now <i class="ion-ios-arrow-forward"></i></a>
			</div>
		</div>
	</div>
	<div class="ft-copyright">
		<div class="ft-left">
			<p>© 2017 Blockbuster. All Rights Reserved. Designed by leehari.</p>
		</div>
		<div class="backtotop">
			<p><a href="#" id="back-to-top">Back to top  <i class="ion-ios-arrow-thin-up"></i></a></p>
		</div>
	</div>
</footer>
<!-- end of footer section-->

<script src="../js/jquery.js"></script>
<script src="../js/plugins.js"></script>
<script src="../js/plugins2.js"></script>
<script src="../js/custom.js"></script>
</body>
</html>