<?php

require '../inc/server.php';
require '../inc/session.php';

// die(json_encode(get_watching_list(true, false), 128));
// die(json_encode(get_watching_list(true, true), 128));

require_login();

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
						<h2 class="section__title">Watching</h2>
						<!-- end section title -->
						<!-- breadcrumb -->
						<ul class="breadcrumb">
							<li class="breadcrumb__item"><a href="../home">Home</a></li>
							<li class="breadcrumb__item breadcrumb__item--active">Watching</li>
						</ul>
						<!-- end breadcrumb -->
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- end page title -->

	<section class="content">
		<div class="content__head">
			<div class="container">
				<div class="row">
					<div class="col-12">

						<!-- content tabs nav -->
						<ul class="nav nav-tabs content__tabs" id="content__tabs" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true">Watching</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" href="#tab-3" role="tab" aria-controls="tab-2" aria-selected="false">Queue</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-3" aria-selected="false">Favourites</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" href="#tab-3" role="tab" aria-controls="tab-4" aria-selected="false">Watched</a>
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

									<li class="nav-item"><a class="nav-link active" id="1-tab" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true">Watching</a></li>

									<li class="nav-item"><a class="nav-link active" id="1-tab" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-2" aria-selected="true">Queue</a></li>

									<li class="nav-item"><a class="nav-link" id="2-tab" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-3" aria-selected="false">Favourites</a></li>

									<li class="nav-item"><a class="nav-link" id="3-tab" data-toggle="tab" href="#tab-3" role="tab" aria-controls="tab-4" aria-selected="false">Watched</a></li>
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

				<!-- Watching -->
				<div class="tab-pane fade show active" id="tab-1" role="tabpanel" aria-labelledby="1-tab">
					<div class="row">
						<?php
						// Everything that we are currently watching but haven't finished.
						$watching_list = get_watching_list(false, false);

						foreach ($watching_list as $watching) {
							// Get the episode with highest number
							$furthest = get_furthest_episode($watching['title'], $watching['type'], false);
							die(json_encode($furthest, 128));
							// Round to an integer
							$watched_progress = round($furthest['time'] / $furthest['time_total'] * 100, 0);
							// Make an sql call to the database / Get the data
							$watch_data = get_content_data($furthest['type'], $furthest['title']);


							?>
							<div class="col-6 col-sm-4 col-lg-3 col-xl-2">
								<div class="card">
									<div class="card__cover">
										<img src="<?php echo authenticate_cdn_url($watch_data['thumbnail']) ?>" alt="" style="width: 100%; height: 255px;">
										<a href=<?php echo '"' . get_furthest_episode_link($watch_data['url'], $watch_data['type'], false) . '"' ?> class="card__play">
											<i class="icon ion-ios-play"></i>
										</a>
									</div>
									<div class="card__content">
										<h3 class="card__title"><a href=<?php echo '"' . get_furthest_episode_link($watch_data['url'], $watch_data['type'], false) . '"' ?>><?php echo $watch_data['title'] ?></a></h3>
										<span class="card__category">
											<a>Released: <?php //echo $watch_data['release_date'] ?></a>
										</span>
										<span class="card__rate"><i class="icon ion-ios-star"></i><?php echo round($watch_data['rating'] / 10, 1) ?></span>
									</div>
								</div>
							</div>
						<?php } ?>

						?>
					</div>
				</div>

			</div>
			<!-- end content tabs -->
		</div>
	</section>

	<!-- catalog -->
	<div class="catalog">
		<div class="container">
			<div class="row">

			</div>
		</div>
	</div>
	<!-- end catalog -->
	<!-- footer -->
	<?= require '../inc/html/footer.php' ?>
	<!-- end footer -->
</body>

</html>