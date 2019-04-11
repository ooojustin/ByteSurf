<?php
    require 'inc/server.php';
    require 'inc/session.php';
    require 'inc/imdb.php';
    require_subscription();  
	date_default_timezone_set('UTC'); 

    if (!isset($_GET['t']))
		die('No anime selected');

	global $db;
    $get_anime = $db->prepare('SELECT * FROM anime WHERE url=:url');
    $get_anime->bindValue(':url', $_GET['t']);   
    $get_anime->execute();   
    $anime = $get_anime->fetch();
    if (!$anime)
        die('No anime found with that title.');
    $url = authenticate_cdn_url($anime['data'], true);  
    $data_raw = file_get_contents($url);
    $json_data = json_decode($data_raw, true);
    // comment out these 2 lines and access all of the data from the $json_data variable
    // $json_encode($json_data, JSON_PRETTY_PRINT);   
	//die();
	if (!isset($_GET['ep'])) {
	    $_GET['ep'] = 1;
	} 
	$episode_info = $json_data['episodeData'][$_GET['ep'] - 1];
	function GenerateAnimeLink($res) {
		// https://cdn.jexflix.com/anime/asobi-asobase/poster.jpg
		// https://cdn.jexflix.com/anime/asobi-asobase/8/1080.mp4
		return "https://cdn.jexflix.com/anime/".$_GET['t']."/".$_GET['ep']."/" . $res . ".mp4";
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
    <link rel="stylesheet" href="css/main.css">

	<!-- Favicons -->
	<link rel="icon" type="image/png" href="../icon/favicon-32x32.png" sizes="32x32">
	<link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">

	<meta name="description" content="">
	<meta name="keywords" content="">A
	<meta name="author" content="Peter Pistachio">
	<title>ByteSurf</title>
</head>
<body class="body">
	
	<!-- header -->
	<?=require 'inc/html/header.php'?>
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
					<h1 class="details__title"><?php echo $json_data['title']; ?></h1>
					<h1 class="details__devices" style="color: #fff"><?php echo 'Episode ' . $_GET['ep']; ?></h1>
				</div>
				<!-- end title -->

			<!-- content -->
			<div class="col-10">
				<div class="card card--details card--series">
					<div class="row">
						<!-- card cover -->
						<div class="col-12 col-sm-4 col-md-4 col-lg-3 col-xl-3">
							<div class="card__cover">
								<img src="<?php  ?>" alt="">
							</div>
						</div>
						<!-- end card cover -->

						<!-- card content -->
						<div class="col-12 col-sm-8 col-md-8 col-lg-9 col-xl-9">
							<div class="card__content">
								<div class="card__wrap">
									<span class="card__rate"><i class="icon ion-ios-star"></i><?=$rating?></span>
									<ul class="card__list">
										<li>HD</li>
									</ul>
								</div>
								<ul class="card__meta">
									<li><span>Genre:</span>
									<? foreach ($genres as $genre) { ?>
									<a href="#"><?php ucwords($genre) ?></a>
									<?php } ?>
									</li>
									<li><span>Release: </span><?php  ?></li>
								</ul>

								<div class="card__description card__description--details">
								<!-- Description -->
								<?php  ?>
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
					<video controls crossorigin playsinline poster="<?php echo authenticate_cdn_url($episode_info['thumbnail']); ?>" id="player">
						<!-- Video files -->
						<?php
							foreach($episode_info['qualities'] as $quality) {
								echo '<source src="'. authenticate_cdn_url(GenerateAnimeLink($quality['resolution'])) . '" type="video/mp4" size="'.$quality['resolution'].'">';
							}
						?>				
					</video>
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
													<th>Episodes</th>
												</tr>
											</thead>
											<tbody>
												<?php 
													foreach($json_data['episodeData'] as $episode) {
												?>
														<tr>
														<th> <a style="color:#ff5860" href="https://bytesurf.io/anime.php?t=<?php $_GET['t'] . '&ep=' . $episode['episode'] ?>"><?php $json_data['title'] .' Episode ' . $episode['episode'] ?></a></td>
														</tr>
													<?php } ?>
											</tbody>
										</table>
									</div>
							</div>
						</div>
					</div>
				<!-- end accordion -->
			</div>
		</div>
		<!-- end details content -->
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