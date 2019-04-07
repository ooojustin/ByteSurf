<?php

    require 'inc/server.php';
    require 'inc/session.php';
    require 'inc/imdb.php';
    require_subscription();
   
	global $user;

    if (!isset($_GET['t']))
        msg('Error', 'Movie not specified.');

    $data = get_movie_data($_GET['t']);
    if (!$data)
        msg('Error', 'Movie not found.');

    update_imdb_information($_GET['t']);
    
    $title = $data['title'];
    $description = $data['description'];
    $thumbnail = authenticate_cdn_url($data['thumbnail']);
    $preview = authenticate_cdn_url($data['preview']);
    $year = $data['year'];
    $certification = $data['certification'];
    $duration = intval($data['duration'] / 60);
    $qualities = authenticated_movie_links($data);
    $rating = $data['rating'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
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
    <!--<div class="details__bg" data-bg="img/home/home__bg.jpg"></div>-->
    <!-- end details background -->
<div class="container">

    <div class="row">

        <!-- player -->
        <div class="col-12">
        <video controls crossorigin playsinline poster=<?=$preview?> id="player">

            <!-- Video files -->
            <? foreach ($qualities as $quality) { ?>
            <source 
                src=<?= '"' . $quality->link . '"' ?> 
                type="video/mp4" 
                size=<?= '"' . $quality->resolution . '"' ?>
            />
            <? } ?>

            <!-- Caption files -->
            <track kind="captions" label="English" srclang="en" src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-HD.en.vtt" default>
            <track kind="captions" label="Français" srclang="fr" src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-HD.fr.vtt">

            <!-- Fallback for browsers that don't support the <video> element -->
            <a href=<?= '"' . $qualities[0]->link . '"' ?> download>Download</a>
        </video>

        </div>
        <!-- end player -->
    </div>

</div>




</section>
<!-- end details -->

<!-- content -->
<section class="content">
    <!-- details content -->
    <div class="container">

        <div class="row">
            <div class="col-12 col-lg-6 ">
                <div class="movie-disc">
                    <div class="movie-cover-img">
                        <div class="card__cover cover-avatar-img">
                            <img src=<?=$thumbnail?> alt="">
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-12 col-lg-6 ">
                <div class="card__content">
                    <div class="content-heading">
                        <h2 class="details__title"><?=$title?></h2>
                    </div>
                    <div class="card__wrap">
                        <span class="card__rate"><i class="icon ion-ios-star"></i><?=$rating?></span>

                        <ul class="card__list">
                            <li>HD</li>
                            <li><?=$certification?></li>
                            <li><?=$year?></li>
                            <li><?=$duration?>min</li>
                        </ul>
                    </div>

                    <ul class="card__meta">
                        <li><span>Genre:</span> <a href="#">Action</a>
                            <a href="#">Triler</a></li>
                        <li><span>Release year:</span> 2017</li>
                        <li><span>Running time:</span> 120 min</li>
                        <li><span>Country:</span> <a href="#">USA</a></li>
                    </ul>
               
                    <span class="card__category">
                        <a href="https://bytesurf.io/contact/?q=problem&t=<?=$title?>" target="blank">Report a Problem</a>
                    </span>
                    
                    <div class="card__description card__description--details">
                        <p>
                            <?=$description?>
                        </p>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- end details content -->
</section>
<!-- end content -->

<!-- similar movies -->
<section>
    <div class="container-fluid">
        <div class="row>">
            <div class="col-12">
                <div class="owl-carousel">

                    <?
                        $similar = get_similar_movies($_GET['t']);
                        foreach ($similar as $movie) {
                            $movie = get_movie_data($movie);
                            $thumbnail = authenticate_cdn_url($movie['thumbnail']);
                            $href = 'https://bytesurf.io/movie.php?t=' . $movie['url'];
                    ?>
                     <div class="card">
                        <div class="card__cover">
                            <img src=<?= '"' . $thumbnail . '"'; ?> alt="">
                            <a href=<?= '"' . $href . '"'; ?> class="card__play">
                                <i class="icon ion-ios-play"></i>
                            </a>
                        </div>
                        <div class="card__content">
                            <h3 class="card__title"><a href=<?= '"' . $href . '"'; ?>><?= $movie['title'] ?></a></h3>
                            <span class="card__category">
                                <a href=<?= '"https://bytesurf.io.com/catalog/?year_min=' . $movie['year'] . '&year_max=' . $movie['year'] . '"' ?>>Released: <?= $movie['year']    ?></a>
                            </span>
                            <span class="card__rate"><i class="icon ion-ios-star"></i><?= $movie['rating'] ?></span>
                        </div>
                    </div>
                    <? } ?>

                </div>
            </div>
        </div>
    </div>
</section>
<!-- end similar movies -->

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