<?php
    // catalog page
    session_start();
    
    if (!isset($_SESSION['id'])) {
        header("location: /login");
        die();
    }
    
   if (isset($_GET['logout'])) {
  	    session_destroy();
  	    unset($_SESSION['id']);
        header("location: ../login");
   	    die();
   }


	require '../inc/server.php';

    // all variables and their default values
    $page = 1; // page #
    $vars = array(
    	'genre' => "Action", 
    	'quality' => 1080, 
    	'imdb_min' => 0.1,
    	'imdb_max' => 10.0, 
    	'year_min' => 2000,
    	'year_max' => 2019
    );

    // vars that are checked via 'LIKE' selection in sql query
    $vars_containify = array(
    	'genre', 
    	'quality'
    );

    //$get_movies = $db->prepare('SELECT * FROM `movies` WHERE LOWER(genres) LIKE :genre AND `qualities` LIKE :quality AND `rating` >= :imdb_min AND `rating` <= :imdb_max AND `year` >= :year_min AND `year` <= :year_max LIMIT 10');
    $querystr = 'SELECT * FROM `movies` WHERE LOWER(genres) LIKE :genre AND `qualities` LIKE :quality AND `rating` >= :imdb_min AND `rating` <= :imdb_max AND `year` >= :year_min AND `year` <= :year_max LIMIT 24';

    if (isset($_GET['page']))
    	$page = intval($_GET['page']);

    foreach ($vars as $var => $default) {

    	// $var = the variable name
    	// $$var = the variable value

    	if (isset($_GET[$var])) 
    		$$var = $_GET[$var];
     	else
     		$$var = $default;

     	$binder = ':' . $var;
     	if (in_array($var, $vars_containify)) {
     		//$get_movies->bindValue($binder, '"%' . strtolower($$var) . '%"');
     		$querystr = str_replace($binder, '"%' . strtolower($$var) . '%"', $querystr);
     		//echo $binder . ' = %' . $$var . '%'; 
     	} else {
     		//$get_movies->bindValue($binder, $$var);
     		$querystr = str_replace($binder, $$var, $querystr);
     		//echo $binder . ' = ' . $$var;
     	}
     	//echo PHP_EOL;

    }

    //echo PHP_EOL . $querystr . PHP_EOL;
    $get_movies = $db->prepare($querystr);
    $get_movies->execute();

   //echo $get_movies->rowCount() . PHP_EOL;

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
	<title>jexflix</title>

</head>
<body class="body">
	
	<!-- header -->
	<header class="header">
		<div class="header__wrap">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<div class="header__content">
							<!-- header logo -->
							<a href="../home" class="header__logo">
								<img src="../img/logo.png" alt="">
							</a>
							<!-- end header logo -->

							<!-- header nav -->
							<ul class="header__nav">
								<!-- dropdown -->
								<li class="header__nav-item">
									<a href="../home" class="header__nav-link">Home</a>
								</li>
								<!-- end dropdown -->

								<!-- catalog -->
								<li class="header__nav-item">
									<a href="../catalog" class="header__nav-link">Catalog</a>
								</li>
								<!-- catalog -->

								<li class="header__nav-item">
									<a href="../pricing" class="header__nav-link">Pricing Plan</a>
								</li>

								<li class="header__nav-item">
									<a href="../faq" class="header__nav-link">Help</a>
								</li>

								<!-- dropdown -->
								<li class="dropdown header__nav-item">
									<a class="dropdown-toggle header__nav-link header__nav-link--more" href="#" role="button" id="dropdownMenuMore" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="icon ion-ios-more"></i></a>

									<ul class="dropdown-menu header__dropdown-menu" aria-labelledby="dropdownMenuMore">
										<li><a href="../about">About</a></li>
										<li><a href="../profile">Profile</a></li>
									</ul>
								</li>
								<!-- end dropdown -->
							</ul>
							<!-- end header nav -->

							<!-- header auth -->
							<div class="header__auth">
								<button class="header__search-btn" type="button">
									<i class="icon ion-ios-search"></i>
								</button>

								<!-- dropdown -->
								<div class="dropdown header__lang">
									<a class="dropdown-toggle header__nav-link" href="#" role="button" id="dropdownMenuLang" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">EN</a>

									<ul class="dropdown-menu header__dropdown-menu" aria-labelledby="dropdownMenuLang">
										<li><a href="#">English</a></li>
										<li><a href="#">Spanish</a></li>
									</ul>
								</div>
								<!-- end dropdown -->

								<a href="index.php?logout=1" class="header__sign-in">
									<i class="icon ion-ios-log-in"></i>
									<span>Sign Out</span>
								</a>
							</div>
							<!-- end header auth -->

							<!-- header menu btn -->
							<button class="header__btn" type="button">
								<span></span>
								<span></span>
								<span></span>
							</button>
							<!-- end header menu btn -->
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- header search -->
		<form action="#" class="header__search">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<div class="header__search-content">
							<input type="text" placeholder="Search for a movie, TV Series that you are looking for">

							<button type="button">search</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<!-- end header search -->
	</header>
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
									<input type="hidden" name="genre" value="Action"/>
									<span></span>
								</div>
								<ul class="filter__item-menu dropdown-menu scrollbar-dropdown" aria-labelledby="filter-genre">
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
					while ($movie = $get_movies->fetch()) { 
						$url = 'https://jexflix.com/movie.php?t=' . $movie['url'];
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
					<ul class="paginator">
						<li class="paginator__item paginator__item--prev">
							<a href="#"><i class="icon ion-ios-arrow-back"></i></a>
						</li>

						<?
							$is_first_page = $page == 1;
							$is_last_page = false; // CHANGE THIS
							// this can probably be done better but im keeping it this way until the backend code is done
							if ($is_first_page) { ?>
							<li class="paginator__item paginator__item--active"><a href="#"><?= $page ?></a></li>
							<li class="paginator__item"><a href="#"><?= $page + 1 ?></a></li>
							<li class="paginator__item"><a href="#"><?= $page + 2 ?></a></li>
							<? } else if ($is_last_page) { ?>
							<li class="paginator__item"><a href="#"><?= $page - 2 ?></a></li>
							<li class="paginator__item"><a href="#"><?= $page - 1 ?></a></li>
							<li class="paginator__item paginator__item--active"><a href="#"><?= $page ?></a></li>
							<? } else { ?>
							<li class="paginator__item"><a href="#"><?= $page - 1 ?></a></li>
							<li class="paginator__item paginator__item--active"><a href="#"><?= $page  ?></a></li>
							<li class="paginator__item"><a href="#"><?= $page + 1 ?></a></li>
							<? }
						?>

						<!--
						old stuff before making this functional
						<li class="paginator__item"><a href="#">1</a></li>
						<li class="paginator__item paginator__item--active"><a href="#">2</a></li>
						<li class="paginator__item"><a href="#">3</a></li>
						-->

						<li class="paginator__item paginator__item--next">
							<a href="#"><i class="icon ion-ios-arrow-forward"></i></a>
						</li>
					</ul>
				</div>
				<!-- end paginator -->

			</div>
		</div>
	</div>
	<!-- end catalog -->

	
	<!-- footer -->
	<footer class="footer">
		<div class="container">
			<div class="row">
				<!-- footer list -->
				<div class="col-6 col-sm-4 col-md-3">
					<h6 class="footer__title">Resources</h6>
					<ul class="footer__list">
						<li><a href="../about">About Us</a></li>
						<li><a href="../pricing">Pricing Plan</a></li>
						<li><a href="../faq">Help</a></li>
					</ul>
				</div>
				<!-- end footer list -->

				<!-- footer list -->
				<div class="col-6 col-sm-4 col-md-3">
					<h6 class="footer__title">Legal</h6>
					<ul class="footer__list">
						<li><a href="../tos">Terms of Use</a></li>
						<li><a href="../privacy">Privacy Policy</a></li>
					</ul>
				</div>
				<!-- end footer list -->

				<!-- footer list -->
				<div class="col-12 col-sm-4 col-md-3">
					<h6 class="footer__title">Contact</h6>
					<ul class="footer__list">
					    <li><a href="../discord">Discord</a></li>
						<li><a href="mailto:support@jexflix.com">support@jexflix.com</a></li>
					</ul>
				</div>
				<!-- end footer list -->

				<!-- footer copyright -->
				<div class="col-12">
					<div class="footer__copyright">
						<small class="section__text">© 2019 jexflix. Created by <a href="https://i.imgur.com/gEZ5bko.jpg" target="_blank">Anthony Almond</a></small>

						<ul>
							<li><a href="../tos">Terms of Use</a></li>
							<li><a href="../privacy">Privacy Policy</a></li>
						</ul>
					</div>
				</div>
				<!-- end footer copyright -->
			</div>
		</div>
	</footer>
	<!-- end footer -->

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
</body>
</html>