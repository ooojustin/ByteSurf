<?php

    require 'inc/server.php';
    require 'inc/session.php';
    require 'inc/imdb.php';
    require_subscription();

    date_default_timezone_set('UTC');

    // make sure the user has provided an anime
    if (!isset($_GET['t']))
        msg('Uh oh :(', 'Please specify an anime.');

    // get data regarding current anime
    $anime = get_anime_data($_GET['t']);
    if (!$anime)
        msg('Uh oh :(', 'We couldn\'t find that anime.');

    // retrieve raw anime data from cdn server
    $url = authenticate_cdn_url($anime['data'], true);
    $data_raw = file_get_contents($url);
    $data = json_decode($data_raw, true);


    // establish anime title/episodes/image links
    $title = $data['title'];
    $episodes = $data['episodeData'];
    $poster = authenticate_cdn_url($data['poster']);
    $cover = authenticate_cdn_url($data['cover']);
    
    // if episode isn't set, default it to 1
    default_get_param('e', 1);

    // get current episode info (note: index = episode # - 1)
    $episode_info = $episodes[$_GET['e'] - 1];

    function generate_mp4_link($res) {
       $format = "https://cdn.bytesurf.io/anime/%s/%s/%s.mp4";
	   $url = sprintf($format, $_GET['t'], $_GET['e'], $res);
	   return $url;
    }

    $submit_watched = $_GET['submit_watched'];
    switch ($submit_watched) {

	   case 1:
		  save_progress_entry(1, 1, 1);
		  break;

	   case 2:
        delete_progress_entry($_GET['t'], 'anime', $_GET['e']);
        break;
    }

    // Check if this anime and episode is watched
    $watched_list = get_progress_tracker_data(true);
    $has_watched = false;
    foreach ($watched_list as $watched) {
        if ($watched['title'] == $_GET['t'] && $watched['episode'] == $_GET['e'])
            $has_watched = true;
    }

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Font -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600%7CUbuntu:300,400,500,700" rel="stylesheet">

        <!-- CSS -->
        <link rel="stylesheet" href="css/bootstrap-reboot.min.css">
        <link rel="stylesheet" href="css/bootstrap-grid.min.css">
        <link rel="stylesheet" href="css/owl.carousel.min.css">
        <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
        <link rel="stylesheet" href="css/nouislider.min.css">
        <link rel="stylesheet" href="css/ionicons.min.css">
        <link rel="stylesheet" href="css/plyr.css">
        <link rel="stylesheet" href="css/photoswipe.css">
        <link rel="stylesheet" href="css/default-skin.css">
        <link href="fonts/fontawesome-free-5.1.0-web/css/all.css" rel="stylesheet">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
        <link rel="stylesheet" href="css/main.css">
        
        <!-- JS -->
        <script src="js/jquery-3.3.1.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/owl.carousel.min.js"></script>
        <script src="js/jquery.mousewheel.min.js"></script>
        <script src="js/jquery.mCustomScrollbar.min.js"></script>
        <script src="js/wNumb.js"></script>
        <script src="js/nouislider.min.js"></script>
        <script src="js/plyr.min.js"></script>
        <script src="js/jquery.morelines.min.js"></script>
        <script src="js/photoswipe.min.js"></script>
        <script src="js/photoswipe-ui-default.min.js"></script>
        <script src="js/main.js"></script>
        <script src="js/progress.tracker.js"></script>

        <!-- PARTY SYSTEM -->
        <? initialize_party_system(); ?>
        <!-- END PARTY SYSTEM -->

        <!-- Favicons -->
        <link rel="icon" type="image/png" href="../icon/favicon-32x32.png" sizes="32x32">
        <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">

        <meta name="description" content="">
        <meta name="keywords" content="">
        <meta name="author" content="Peter Pistachio">

        <title>ByteSurf</title>

</head>
<body class="body">



	<!-- header -->

	<?= require 'inc/html/header.php' ?>

	<!-- end header -->



	<!-- details -->

	<section class="section details">

		<!-- details background -->

		<!-- <div class="details__bg" data-bg="img/home/home__bg.jpg"></div> -->

		<!-- end details background -->

		<!-- details content -->

		<div class="container">

			<div class="row">

				<!-- title -->

				<div class="col-12">

					<h1 class="details__title"><?= $title ?></h1>

					<h1 class="details__devices" style="color: #fff"><?php echo 'Episode ' . $_GET['e']; ?></h1>

					<h1 class="details__devices" style="color: #fff"><?php echo  $episode_info['episode_title']; ?></h1>

				</div>

				<!-- end title -->



				<!-- content -->

				<div class="col-10">

					<div class="card card--details card--series">

						<div class="row">

							<!-- card cover -->

							<div class="col-12 col-sm-4 col-md-4 col-lg-3 col-xl-3">

								<div class="card__cover">

									<img src="<?= $poster ?>" alt="">

								</div>

							</div>

							<!-- end card cover -->

							<!-- card content -->

							<div class="col-12 col-sm-8 col-md-8 col-lg-9 col-xl-9">

								<div class="card__content">

									<div class="card__wrap">

										<span class="card__rate"><i class="icon ion-ios-star"></i><?php echo round($anime['rating'] / 10, 1); ?></span>

										<ul class="card__list">

											<li>HD</li>

										</ul>

									</div>



									<ul class="card__meta">

										<li><span>Genre:</span>

											<?php

											$genres_json = json_decode($anime['genres'], true);

											foreach ($genres_json as $genre) {

												?>

												<a href="#"><?= ucwords($genre) ?></a>

											<?php } ?>

										</li>

										<li><span>Release year: </span><?php echo $anime['release_date']; ?></li>

									</ul>



									<div class="card__description card__description--details">

										<?php echo $data['synopsis']; ?>

									</div>

								</div>

							</div>

							<!-- end card content -->

						</div>

					</div>

				</div>

				<!-- end content -->



				<!-- player -->

				<div class="col-12">

					<video controls crossorigin playsinline poster="<?= $cover ?>" id="player">

						<!-- Video files -->

						<?

						foreach ($episode_info['qualities'] as $quality) {

							$res = $quality['resolution'];

							$url = generate_mp4_link($res);

							$url = authenticate_cdn_url($url);

							?>

							<source src="<?= $url ?>" type="video/mp4" size="<?= $res ?>" />

						<? } ?>



						<!-- Fallback for browsers that don't support the <video> element -->

						<a href="<?= generate_mp4_link($episode_info['qualities'][0]['resolution']) ?>" download>Download</a>



					</video>



					<!-- Queue btn -->

					<form action="" method="get">

						<input type="hidden" name="e" value="<?php echo htmlspecialchars($_GET['e']); ?>">

						<input type="hidden" name="t" value="<?php echo htmlspecialchars($_GET['t']); ?>">

						<input type="hidden" name="s" value="-1">

						<input type="hidden" name="type" value="anime">



						<style>

							.btn {

								background-color: DodgerBlue;

								border: none;

								color: white;

								padding: 12px 16px;

								font-size: 16px;

								cursor: pointer;

							}



							/* Darker background on mouse-over */

							.btn:hover {

								background-color: RoyalBlue;

							}

						</style>



						<?php if ($has_watched) { ?>

							<div style="float: right; padding-top: 10px; padding-left: 10px;">

								<button class="filter__btn btn" name="submit_queue" type="submit" value="2" style="font-size: 10px; height: 35px; width: 85px; display: block;"><i class="fas fa-bookmark"></i> Queue</button>

							</div>

						<?php } else { ?>

							<div style="float: right; padding-top: 10px; padding-left: 10px;">

								<button class="filter__btn btn" name="submit_queue" type="submit" value="1" id="submit_queue" style="font-size: 10px; height: 35px; width: 85px; display: block;"><i class="fas fa-bookmark"></i> Queue</button>

							</div>

						<?php } ?>

					</form>



					<!-- End Queue Button -->



					<!-- Watched btn -->

					<form action="" method="get">

						<input type="hidden" name="e" value="<?php echo htmlspecialchars($_GET['e']); ?>">

						<input type="hidden" name="t" value="<?php echo htmlspecialchars($_GET['t']); ?>">

						<input type="hidden" name="s" value="-1">

						<input type="hidden" name="type" value="anime">

						<?php if ($has_watched) { ?>

							<div style="float: right; padding-top: 10px;">

								<button class="filter__btn" name="submit_watched" type="submit" value="2" style="font-size: 10px; height: 35px; width: 160px;">Remove from Watched</button>

							</div>

						<?php } else { ?>

							<div style="float: right; padding-top: 10px;">

								<button class="filter__btn" name="submit_watched" type="submit" value="1" id="submit_watched" style="font-size: 10px; height: 35px; width: 120px;">Add to Watched</button>

							</div>

						<?php } ?>

					</form>

					<!-- end Watched btn -->



				</div>

				<!-- end player -->

			</div>

		</div>

		<!-- end details content -->

	</section>

	<!-- end details -->

	<!-- content -->

	<section class="content">

		<!-- details content -->

		<div class="container">

			<div class="row">

				<!-- accordion -->

				<div class="col-12 col-lg-6" style="max-width: 100%; flex: 100%">

					<div class="accordion" id="accordion">

						<div class="accordion__card">

							<div class="card-body">

								<table class="accordion__list">

									<thead>

										<tr>

											<th style="color:#ff5860">#</th>

											<th style="color:#ff5860">Title</th>

											<th style="color:#ff5860">Air Date</th>

											<th style="color:#ff5860">Watched</th>



										</tr>

									</thead>

									<tbody>

										<?php foreach ($episodes as $episode) {

											$episode_link = "https://bytesurf.io/anime.php?t=" . $_GET['t'] . '&e='	. $episode['episode'];

											$color = ($episode['episode'] == $_GET['e']) ? '#ff5860' : 'rgba(255,255,255,0.7)';

											?>

											<tr>

												<th><a href="<?= $episode_link ?>" style="color:<?= $color ?>"><?= $episode['episode'] ?><a></th>

												<?php if ($episode['episode_title'] != "") { ?>

													<td><a href="<?= $episode_link ?>" style="color:<?= $color ?>"><?= $episode['episode_title'] ?></a></td>

												<?php } else { ?>

													<td><a href="<?= $episode_link ?>" style="color:<?= $color ?>">-</a></td>

												<?php }

											if ($episode['air_date'] != "") { ?>

													<td><a href="<?= $episode_link ?>" style="color:<?= $color ?>"><?= $episode['air_date'] ?></a></td>

												<?php } else { ?>

													<td><a href="<?= $episode_link ?>" style="color:<?= $color ?>">-</a></td>

												<?php } ?>

												<?php

												$has_watched = false;

												foreach ($watched_list as $watched) {

													if ($watched['title'] == $_GET['t'] && $watched['episode'] == $episode['episode'])

														$has_watched = true;

												}

												?>

												<td><a href="<?= $episode_link ?>" style="color:<?= $color ?>"><?php if ($has_watched) echo '✔';

																												else echo '✘'; ?></a></td>

											</tr>

										<? } ?>

									</tbody>

								</table>

							</div>

						</div>

					</div>

				</div>

				<!-- end accordion -->



				<!-- end content -->

				<!-- footer -->

				<?= require 'inc/html/footer.php' ?>

				<!-- end footer -->

				<!-- Root element of PhotoSwipe. Must have class pswp. -->

				<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">

					<!-- Background of PhotoSwipe. 

			It's a separate element, as animating opacity is faster than rgba(). -->

					<div class="pswp__bg"></div>

					<!-- Slides wrapper with overflow:hidden. -->

					<div class="pswp__scroll-wrap">

						<!-- Container that holds slides. PhotoSwipe keeps only 3 slides in DOM to save memory. -->

						<!-- don't modify these 3 pswp__item elements, data is added later on. -->

						<div class="pswp__container">

							<div class="pswp__item"></div>

							<div class="pswp__item"></div>

							<div class="pswp__item"></div>

						</div>

						<!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->

						<div class="pswp__ui pswp__ui--hidden">

							<div class="pswp__top-bar">

								<!--  Controls are self-explanatory. Order can be changed. -->

								<div class="pswp__counter"></div>

								<button class="pswp__button pswp__button--close" title="Close (Esc)"></button>

								<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>

								<!-- Preloader -->

								<div class="pswp__preloader">

									<div class="pswp__preloader__icn">

										<div class="pswp__preloader__cut">

											<div class="pswp__preloader__donut"></div>

										</div>

									</div>

								</div>

							</div>

							<button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>

							<button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>

							<div class="pswp__caption">

								<div class="pswp__caption__center"></div>

							</div>

						</div>

					</div>

				</div>

</body>



</html>