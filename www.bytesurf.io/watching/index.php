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

	<!-- content -->
	<section class="content">
		<div class="content__head">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<!-- content title -->
						<h2 class="content__title">Watching</h2>
						<!-- end content title -->

						<!-- content tabs nav -->
						<ul class="nav nav-tabs content__tabs" id="content__tabs" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true">Watching</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="false">Queue</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" href="#tab-3" role="tab" aria-controls="tab-3" aria-selected="false">Favourites</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" href="#tab-4" role="tab" aria-controls="tab-4" aria-selected="false">Watched</a>
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

									<li class="nav-item"><a class="nav-link" id="2-tab" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="false">Queue</a></li>

									<li class="nav-item"><a class="nav-link" id="3-tab" data-toggle="tab" href="#tab-3" role="tab" aria-controls="tab-3" aria-selected="false">Favourites</a></li>

									<li class="nav-item"><a class="nav-link" id="4-tab" data-toggle="tab" href="#tab-4" role="tab" aria-controls="tab-4" aria-selected="false">Watched </a></li>
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
						// Everything that we are currently watching but haven't finished.
						foreach (get_watching_list(false) as $watching) {
							$watched_progress = round($watching['time'] / $watching['time_total'] * 100, 0);
							$watch_data = get_content_data($watching['type'], $watching['title']);
							?>
							<div class="col-6 col-sm-4 col-lg-3 col-xl-2">
								<div class="card">
									<div class="card__cover">
										<img src="<?= authenticate_cdn_url($watch_data['thumbnail']) ?>" alt="" style="width: 100%; height: 255px;">
										<a href="<?= get_furthest_episode_link($watch_data['url'], $watching['type'], false) ?>" class="card__play">
											<i class="icon ion-ios-play"></i>
										</a>
									</div>
									<div class="card__content">
										<h3 class="card__title"><a href="<? get_furthest_episode_link($watch_data['url'], $watching['type'], false) ?>"><?= $watch_data['title'] ?></a></h3>
										<span class="card__category">
											<? if ($watching['type'] == "show") { ?>
												<a>Season: <?= $watching['season'] ?></a>
											<? } ?>
											<?php if ($watching['type'] != "movie") { ?>
												<a>Episode: <?= $watching['episode'] ?></a>
											<?php } ?>
										</span>
									</div>
								</div>
							</div>
						<? } ?>
					</div>
				</div>

				<div class="tab-pane fade" id="tab-2" role="tabpanel" aria-labelledby="2-tab">
					<div class="row">



					</div>
				</div>

				<div class="tab-pane fade" id="tab-3" role="tabpanel" aria-labelledby="3-tab">
					<div class="row">


					</div>
				</div>

				<div class="tab-pane fade" id="tab-4" role="tabpanel" aria-labelledby="4-tab">
					<div class="row">
						<?
						// Everything that we have already watched and finished.
						foreach (get_watching_list(true) as $watching) {
							$watched_progress = round($watching['time'] / $watching['time_total'] * 100, 0);
							$watched_data = get_content_data($watching['type'], $watching['title']);
							?>
							<div class="col-6 col-sm-4 col-lg-3 col-xl-2">
								<div class="card">
									<div class="card__cover">
										<img src="<?= authenticate_cdn_url($watched_data['thumbnail']) ?>" alt="" style="width: 100%; height: 255px;">
										<a href="<?= get_furthest_episode_link($watched_data['url'], $watching['type'], true) ?>" class="card__play">
											<i class="icon ion-ios-play"></i>
										</a>
									</div>
									<div class="card__content">
										<h3 class="card__title"><a href="<?= get_furthest_episode_link($watched_data['url'], $watching['type'], true) ?>"><?= $watched_data['title'] ?></a></h3>
										<span class="card__category">
											<? if ($watching['type'] == "show") { ?>
												<a>Season: <?= $watching['season'] ?></a>
											<? } ?>
											<?php if ($watching['type'] != "movie") { ?>
												<a>Episode: <?= $watching['episode'] ?></a>
											<?php } ?>
										</span>
									</div>
								</div>
							</div>
						<? } ?>
					</div>
				</div>

			</div>
			<!-- end content tabs -->
		</div>
	</section>
	<!-- end content -->

	<!-- footer -->
	<?= require '../inc/html/footer.php' ?>
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
