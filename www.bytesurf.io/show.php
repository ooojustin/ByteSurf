<?php

    require 'inc/server.php';
    require 'inc/session.php';
    require 'inc/imdb.php';
    require_subscription();
    
    date_default_timezone_set('UTC'); // why?
   
	global $user;
	
    if (!isset($_GET['t']))
        msg('Error', 'Show not specified.');
        
    $data = get_series_data($_GET['t']);
    if (!$data)
        msg('Error', 'Show not found.');
        
    $title = $data['title'];
    $description = $data['description'];
    $thumbnail = authenticate_cdn_url($data['thumbnail']);
    $preview = authenticate_cdn_url($data['preview']);
    $year = $data['year'];
    $certification = $data['certification'];
    $rating = $data['rating'];
    $series_data_url = authenticate_cdn_url($data['data'], true);
    $series_data_raw = get_request($series_data_url); // note: using 'get_request' instead of 'file_get_contents'
    $series_data = json_decode($series_data_raw, true);
    $genres = json_decode($data['genres']);    
    $show_url_built = "https://bytesurf.io/show.php?t=" . $_GET['t'];
    
    if (isset($_GET['s']) && isset($_GET['e'])) {
        $specific_data_url = "https://cdn.bytesurf.io/shows/" . $data['url'] . "/" . $_GET['s'] . "/" . $_GET['e'] . "/";
        $specific_data_url = authenticate_cdn_url($specific_data_url . 'data.json', true);
        $specific_data_raw = get_request($specific_data_url);
        $specific_data = json_decode($specific_data_raw, true);
        $title = $title . " - " . $specific_data['title'];
        $description = $specific_data['description'];
    } else {
        $specific_data_url = "https://cdn.bytesurf.io/shows/" . $data['url'] . "/" . 1 . "/" . 1 . "/";
        $specific_data_url = authenticate_cdn_url($specific_data_url . "data.json", true);
        $specific_data_raw = get_request($specific_data_url);
        $specific_data = json_decode($specific_data_raw, true);
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
		<div class="details__bg" data-bg="<?=$preview?>"></div>
		<!-- end details background -->

		<!-- details content -->
		<div class="container">
			<div class="row">
				<!-- title -->
				<div class="col-12">
					<h1 class="details__title"><?=$title?></h1>
				</div>
				<!-- end title -->

				<!-- content -->
				<div class="col-10">
					<div class="card card--details card--series">
						<div class="row">
							<!-- card cover -->
							<div class="col-12 col-sm-4 col-md-4 col-lg-3 col-xl-3">
								<div class="card__cover">
									<img src="<?=$thumbnail?>" alt="">
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
											<li><?=$certification?></li>
										</ul>
									</div>

									<ul class="card__meta">
										<li><span>Genre:</span>
										<? foreach ($genres as $genre) { ?>
										<a href="#"><?=ucwords($genre)?></a>
										<? } ?>
										</li>
										<li><span>Release year: </span><?=$year?></li>
										<li><span>View on: </span><a href="https://www.imdb.com/title/<?=$data['imdb_id']?>" target="blank">IMDB</a></li>
									</ul>

									<div class="card__description card__description--details">
										<?=$description?>
									</div>
								</div>
							</div>
							<!-- end card content -->
						</div>
					</div>
				</div>
				<!-- end content -->

				<!-- player -->
				<div class="col-12 col-xl-6">
					<video controls crossorigin playsinline poster="<?=$preview?>" id="player">
						<!-- Video files -->
						
                        <? foreach ($specific_data['qualities'] as $quality) { ?>
                         <source 
                             src=<?= '"' . authenticate_cdn_url($specific_data_url . $quality['resolution'] . ".mp4") . '"' ?> 
                             type="video/mp4" 
                             size=<?= '"' . $quality['resolution'] . '"' ?>
                         />
                         <? } ?>

						<!-- Caption files -->
						<track kind="captions" label="English" srclang="en" src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-HD.en.vtt"
						    default>
						<track kind="captions" label="Français" srclang="fr" src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-HD.fr.vtt">

						<!-- Fallback for browsers that don't support the <video> element -->
						<a href="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4" download>Download</a>
					</video>
				</div>
				<!-- end player -->
				
				

				<!-- accordion -->
				<div class="col-12 col-xl-6">
					<div class="accordion" id="accordion">
						<div class="accordion__card">
						    
						    <? foreach ($series_data['seasons'] as $seasons) {
						    
						        $season_data_url = "https://cdn.bytesurf.io/shows/" . $data['url'] . "/" . $seasons['season'] . "/data.json";
						        $season_data = json_decode(file_get_contents(authenticate_cdn_url($season_data_url, true)), true);
						    ?>
						    
						    
						    
							<div class="card-header" id="heading<?=$seasons['season']?>">
								<button type="button" data-toggle="collapse" data-target="#collapse<?=$seasons['season']?>" aria-expanded="false" aria-controls="collapse<?=$seasons['season']?>">
									<span><?=$seasons['title']?></span>
									<span><?=count($season_data['episodes']) ?> Episodes from <?=date("F, Y", time() - $season_data['episodes'][0]['released']) ?> until <?=date("F, Y", time() - $season_data['episodes'][count($season_data['episodes']) - 1]['released']) ?></span>
								</button>
							</div>

							<div id="collapse<?=$seasons['season']?>" class="collapse hide" aria-labelledby="heading<?=$seasons['season']?>" data-parent="#accordion">
								<div class="card-body">
									<table class="accordion__list">
										<thead>
											<tr>
												<th>#</th>
												<th>Title</th>
												<th>Air Date</th>
											</tr>
										</thead>

										<tbody>
										    
										    <? foreach ($season_data['episodes'] as $episodes) { ?>
											<tr>
											    <? $url = $show_url_built . '&s=' . $seasons['season'] . '&e=' . $episodes['episode']; ?>
												<th><a href="<?=$url?>" style="color: rgba(255,255,255,0.7)"><?=$episodes['episode']?><a></th>
												<td><a href="<?=$url?>" style="color: rgba(255,255,255,0.7)"><?=$episodes['title']?></a></td>
												<td><a href="<?=$url?>" style="color: rgba(255,255,255,0.7)"><?=date("F j, Y", time() - $episodes['released']) ?></a></td>
											</tr>
											</a>
											<? } ?>
											
											
										</tbody>
									</table>
								</div>
							</div>
							
								<? } ?>
						</div>
					</div>
				</div>
				<!-- end accordion -->
			
				
			</div>
		</div>
		<!-- end details content -->
	</section>
	<!-- end details -->
	

	<!-- footer -->
	<?=require 'inc/html/footer.php'?>
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
</body>
</html>