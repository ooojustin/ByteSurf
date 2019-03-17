<?php
    // details page
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

	require 'inc/server.php';
	require 'inc/session.php';
	
    if (!isset($_GET['t']))
    	die('No anime selected');

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
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initgial-scale=1, shrink-to-fit=no">

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
									<a class="dropdown-toggle header__nav-link" href="#" role="button" id="dropdownMenuLang" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Catalog</a>
										<ul class="dropdown-menu header__dropdown-menu" aria-labelledby="dropdownMenuLang">
											<li><a href="../catalog">Movies</a></li>
											<li><a href="index.php">Anime</a></li>
									</ul>
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
        <form action="https://jexflix.com/anime" method="get" class="header__search">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="header__search-content">
                            <input type="text" id="search" name='search' placeholder="Search for an anime that you are looking for">
                            <button type="submit">search</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- end header search -->
	</header>
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
														echo '<tr>';
														echo '<th> <a style="color:#ff5860" href="https://jexflix.com/anime.php?t='. $_GET['t'] . '&ep=' . $episode['episode'] . '">'.$json_data['title'] .' Episode ' . $episode['episode']. '</a> </td>';
														echo '</tr>';
													}
												?>
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