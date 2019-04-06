<?php

    

    require '../inc/server.php';

    require '../inc/session.php';

    require_subscription();

    

    $trending = array(

        'triple-frontier-2019',

        'mortal-engines-2018',

        'overlord-2018',

        'aquaman-2018',

        'isnt-it-romantic-2019',

        'spider-man-into-the-spider-verse-2018',

        'ralph-breaks-the-internet-2018',

        'crazy-rich-asians-2018'

    );

    

    $new_releases = array(

        'creed-ii-2018',

        'deadpool-2-2018',

        'bumblebee-2018',

        'venom-2018',

        'bohemian-rhapsody-2018',

        'fantastic-beasts-the-crimes-of-grindelwald-2018',

        'hunter-killer-2018',

        'jurassic-world-fallen-kingdom-2018'

    );

    

    $explore_movies = array(

        'halloween-2018', // 1

        'the-meg-2018', // 2

        'split-2016', // 3

        'passengers-2016', // 4

        'john-wick-chapter-2-2017', // 5

        'robin-hood-2018', // 6

        'the-wolf-of-wall-street-2013', // 7

        'mile-22-2018', // 8

        'hacksaw-ridge-2016', // 9

        'incredibles-2-2018', // 10

        'the-grinch-2018', // 11

        'avengers-infinity-war-2018', // 12

        'first-man-2018', // 13

        'polar-2019', // 14

        'the-girl-in-the-spiders-web-2018', // 15

        'baby-driver-2017', // 16

        'the-kissing-booth-2018', // 17

        'upgrade-2018', // 18

    );

    

    global $db;

    

	$get_movie_count = $db->prepare('SELECT * FROM movies');

	$get_movie_count->execute();

	$movie_count = $get_movie_count->rowCount();

	

	$get_anime_count = $db->prepare('SELECT * FROM anime');

	$get_anime_count->execute();

	$anime_count = $get_anime_count->rowCount();

	

	$get_series_count = $db->prepare('SELECT * FROM series');

	$get_series_count->execute();

	$series_count = $get_series_count->rowCount();

    

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

									<a href="../random.php" class="header__nav-link">Random</a>

								</li>



								<li class="header__nav-item">

									<a href="../about" class="header__nav-link">About</a>

								</li>





							</ul>

							<!-- end header nav -->



							<!-- header auth -->

							<div class="header__auth">

							    

								<button class="header__search-btn" type="button">

									<i class="icon ion-ios-search"></i>

								</button>



								<div class="dropdown header__lang">

									<a class="dropdown-toggle header__nav-link" href="#" role="button" id="dropdownMenuLang" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=$user['username']?></a>



									<ul class="dropdown-menu header__dropdown-menu" aria-labelledby="dropdownMenuLang">

										<li><a href="../profile">Profile</a></li>

										<? if (is_administrator()) { ?><li><a href="../admin">Administration</a></li><? } ?>

										<li><a href="index.php?logout=1">Sign Out</a></li>

									</ul>

								</div>

							</div>

							<!-- end header auth -->

						</div>

					</div>

				</div>

			</div>

		</div>



        <!-- header search -->

        <form action="https://jexflix.com/catalog" method="get" class="header__search">

            <div class="container">

                <div class="row">

                    <div class="col-12">

                        <div class="header__search-content">

                            <input type="text" id="search" name='search' placeholder="Search for a movie, TV Series that you are looking for">



                            <button type="submit">search</button>

                        </div>

                    </div>

                </div>

            </div>

        </form>

        <!-- end header search -->

	</header>

	<!-- end header -->

	

    <div id="dabar" class="hide_on_mobile" style="margin-top: 80px; background: rgba(10, 10, 10, 0.6);"><b><span style="background-image: -webkit-linear-gradient(0deg, #ff55a5 0%, #ff5860 100%); -webkit-text-fill-color: transparent; -webkit-background-clip: text;">We currently have <?=$movie_count?> movies, <?=$series_count?> TV shows, and <?=$anime_count?> animes. Refresh for the newest info.</span></b></div>

	<!-- home -->

	<section class="home" style="margin-top: 0px;">

		<!-- home bg -->

		<div class="owl-carousel home__bg">

			<div style="color:#221f30"></div>

		</div>

		<!-- end home bg -->



		<div class="container">

			<div class="row">

				<div class="col-12">

					<h1 class="home__title"><b>TRENDING</b></h1>



					<button class="home__nav home__nav--prev" type="button">

						<i class="icon ion-ios-arrow-round-back"></i>

					</button>

					<button class="home__nav home__nav--next" type="button">

						<i class="icon ion-ios-arrow-round-forward"></i>

					</button>

				</div>



				<div class="col-12">

					<div class="owl-carousel home__carousel">

					    

					    <?

					        foreach ($trending as $movie) {

					            $data = get_movie_data($movie);

					            $url = "../movie.php?t=" . $data['url'];

					            $genres = json_decode($data['genres']);

					    ?>

					    

						<div class="item">

							<!-- card -->

							<div class="card card--big">

								<div class="card__cover">

									<img src="<?=authenticate_cdn_url($data['thumbnail'])?>" alt="">

									<a href=<?=$url?> class="card__play">

										<i class="icon ion-ios-play"></i>

									</a>

								</div>

								<div class="card__content">

									<h3 class="card__title"><a href=<?=$url?>><?=$data['title']?></a></h3>

									<span class="card__category">

										<a href="#"><?=ucwords($genres[0])?></a>

										<a href="#"><?=ucwords($genres[1])?></a>

									</span>

									<span class="card__rate"><i class="icon ion-ios-star"></i><?=$data['rating']?></span>

								</div>

							</div>

							<!-- end card -->

						</div>

						

						<? } ?>

						

					</div>

				</div>

			</div>

		</div>

	</section>

	<!-- end home -->



	<!-- content -->

	<section class="content">

		<div class="content__head">

			<div class="container">

				<div class="row">

					<div class="col-12">

						<!-- content title -->

						<h2 class="content__title">Explore</h2>

						<!-- end content title -->



						<!-- content tabs nav -->

						<ul class="nav nav-tabs content__tabs" id="content__tabs" role="tablist">

							<li class="nav-item">

								<a class="nav-link active" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true">NEW RELEASES</a>

							</li>



							<li class="nav-item">

								<a class="nav-link" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="false">MOVIES</a>

							</li>



							<li class="nav-item">

								<a class="nav-link" data-toggle="tab" href="#tab-3" role="tab" aria-controls="tab-3" aria-selected="false">TV SERIES</a>

							</li>



							<li class="nav-item">

								<a class="nav-link" data-toggle="tab" href="#tab-4" role="tab" aria-controls="tab-4" aria-selected="false">ANIME</a>

							</li>

						</ul>

						<!-- end content tabs nav -->



						<!-- content mobile tabs nav -->

						<div class="content__mobile-tabs" id="content__mobile-tabs">

							<div class="content__mobile-tabs-btn dropdown-toggle" role="navigation" id="mobile-tabs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

								<input type="button" value="New items">

								<span></span>

							</div>



							<div class="content__mobile-tabs-menu dropdown-menu" aria-labelledby="mobile-tabs">

								<ul class="nav nav-tabs" role="tablist">

									<li class="nav-item"><a class="nav-link active" id="1-tab" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true">NEW RELEASES</a></li>



									<li class="nav-item"><a class="nav-link" id="2-tab" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="false">MOVIES</a></li>



									<li class="nav-item"><a class="nav-link" id="3-tab" data-toggle="tab" href="#tab-3" role="tab" aria-controls="tab-3" aria-selected="false">TV SERIES</a></li>



									<li class="nav-item"><a class="nav-link" id="4-tab" data-toggle="tab" href="#tab-4" role="tab" aria-controls="tab-4" aria-selected="false">CARTOONS</a></li>

								</ul>

							</div>

						</div>

						<!-- end content mobile tabs nav -->

					</div>

				</div>

			</div>

		</div>



		<div class="container">

			<!-- content tabs -->

			<div class="tab-content" id="myTabContent">

				<div class="tab-pane fade show active" id="tab-1" role="tabpanel" aria-labelledby="1-tab">

					<div class="row">

					    

					    <?

					        foreach ($new_releases as $movie) {

					            $data = get_movie_data($movie);

					            $url = "../movie.php?t=" . $data['url'];

					            $genres = json_decode($data['genres']);

					    ?>

					    

						<!-- card -->

						<div class="col-6 col-sm-12 col-lg-6">

							<div class="card card--list">

								<div class="row">

									<div class="col-12 col-sm-4">

										<div class="card__cover">

											<img src="<?=authenticate_cdn_url($data['thumbnail'])?>" alt="">

											<a href="<?=$url?>" class="card__play">

												<i class="icon ion-ios-play"></i>

											</a>

										</div>

									</div>



									<div class="col-12 col-sm-8">

										<div class="card__content">

											<h3 class="card__title"><a href="<?=$url?>"><?=$data['title']?></a></h3>

											<span class="card__category">

												<a href="#"><?=ucwords($genres[0])?></a>

												<a href="#"><?=ucwords($genres[1])?></a>

											</span>



											<div class="card__wrap">

												<span class="card__rate"><i class="icon ion-ios-star"></i><?=$data['rating']?></span>



												<ul class="card__list">

													<li><?=$data['certification']?></li>

													<li><?=$data['year']?></li>

												</ul>

											</div>



											<div class="card__description">

												<p><?=$data['description']?></p>

											</div>

										</div>

									</div>

								</div>

							</div>

						</div>

						<!-- end card -->

						<? } ?>

					</div>

				</div>



				<div class="tab-pane fade" id="tab-2" role="tabpanel" aria-labelledby="2-tab">

					<div class="row">

					    

					    <?

					        foreach ($explore_movies as $movie) {

					            $data = get_movie_data($movie);

					            $url = "../movie.php?t=" . $data['url'];

					            $genres = json_decode($data['genres']);

					    ?>

					    

						<!-- card -->

						<div class="col-6 col-sm-4 col-lg-3 col-xl-2">

							<div class="card">

								<div class="card__cover">

									<img src="<?=authenticate_cdn_url($data['thumbnail'])?>" alt="">

									<a href="<?=$url?>" class="card__play">

										<i class="icon ion-ios-play"></i>

									</a>

								</div>

								<div class="card__content">

									<h3 class="card__title"><a href="<?=$url?>"><?=$data['title']?></a></h3>

									<span class="card__category">

											<a href="#"><?=ucwords($genres[0])?></a>

											<a href="#"><? if (isset($genres[1])) { echo ucwords($genres[1]); }?></a>

									</span>

									<span class="card__rate"><i class="icon ion-ios-star"></i><?=$data['rating']?></span>

								</div>

							</div>

						</div>

						<!-- end card -->

						<? } ?>



					</div>

				</div>



				<div class="tab-pane fade" id="tab-3" role="tabpanel" aria-labelledby="3-tab">

				    <h1>Coming Soon</h1>

				</div>



				<div class="tab-pane fade" id="tab-4" role="tabpanel" aria-labelledby="4-tab">

				    <h1>Coming Soon</h1>

				    

				</div>

			</div>

			<!-- end content tabs -->

		</div>

	</section>

	<!-- end content -->





	<!-- footer -->

	<footer class="footer">

		<div class="container">

			<div class="row">

				<!-- footer list -->

				<div class="col-6 col-sm-4 col-md-3">

					<h6 class="footer__title">Resources</h6>

					<ul class="footer__list">

						<li><a href="#">About Us</a></li>

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