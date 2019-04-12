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

    $title = $data['title'];
    $episodes = $data['episodeData'];
    $poster = authenticate_cdn_url(str_replace('cdn.jexflix.com', 'cdn.bytesurf.io', $data['poster']));

	if (!isset($_GET['ep']))
	    $_GET['ep'] = 1;

	$episode_info = $episodes[$_GET['ep'] - 1];

	function generate_mp4_link($res) {
        $format = "https://cdn.bytesurf.io/anime/%s/%s/%s.mp4";
        $url = sprintf($format, $_GET['t'], $_GET['ep'], $res);
        return $url;
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
					<h1 class="details__title"><?= $title ?></h1>
					<h1 class="details__devices" style="color: #fff"><?php echo 'Episode ' . $_GET['ep']; ?></h1>
				</div>
				<!-- end title -->

				<!-- player -->
				<div class="col-12">
					<video controls crossorigin playsinline poster="<?=$poster?>" id="player">
						<!-- Video files -->
						<?
							foreach($episode_info['qualities'] as $quality) {
                                $res = $quality['resolution'];
                                $url = generate_mp4_link($res);
                                $url = authenticate_cdn_url($url);
                        ?>
                        <source 
                            src="<?= $url ?>"
                            type="video/mp4" 
                            size="<?= $res ?>"
                        />
                        <? } ?>
                        
                        <!-- Fallback for browsers that don't support the <video> element -->
                        <a href=<?= '"' . $episode_info['qualities'][0]['link'] . '"' ?> download>Download</a>
                        
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
												<? foreach($episodes as $episode) { ?>
                                                    <? $episode_url = sprintf('%s&ep=%s', $_GET['t'], $episode['episode']); ?>
                                                    <tr><th> 
                                                    <a style="color:#ff5860" href="https://bytesurf.io/anime.php?t=<?= $episode_url ?>">
                                                        <?= $title .' Episode ' . $episode['episode'] ?>
                                                    </a>
                                                    </th></tr>
												<? } ?>
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