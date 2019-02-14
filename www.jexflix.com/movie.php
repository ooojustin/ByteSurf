<?php
    
    require 'inc/server.php';

    $data = get_movie_data($_GET["t"]);
    if (!$data)
        die('Movie not found.'); // make a 404 page or smth

    $title = $data['title'];
    $description = $data['description'];
    $thumbnail = authenticate_cdn_url($data['thumbnail']);
    $preview = authenticate_cdn_url($data['preview']);
    $year = $data['year'];
    $certification = $data['certification'];
    $duration = intval($data['duration'] / 60);
    $qualities = authenticated_movie_links($data);

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
	<link rel="icon" type="image/png" href="icon/favicon-32x32.png" sizes="32x32">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

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
                        <a href="index.html" class="header__logo">
                            <img src="img/logo.svg" alt="">
                        </a>
                        <!-- end header logo -->

                        <!-- header nav -->
                        <ul class="header__nav">
                            <!-- dropdown -->
                            <li class="header__nav-item">
                                <a class="dropdown-toggle header__nav-link" href="#" role="button" id="dropdownMenuHome" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Home</a>

                                <ul class="dropdown-menu header__dropdown-menu" aria-labelledby="dropdownMenuHome">
                                    <li><a href="index.html">Home slideshow bg</a></li>
                                    <li><a href="index2.html">Home static bg</a></li>
                                </ul>
                            </li>
                            <!-- end dropdown -->

                            <!-- dropdown -->
                            <li class="header__nav-item">
                                <a class="dropdown-toggle header__nav-link" href="#" role="button" id="dropdownMenuCatalog" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Catalog</a>

                                <ul class="dropdown-menu header__dropdown-menu" aria-labelledby="dropdownMenuCatalog">
                                    <li><a href="catalog1.html">Catalog Grid</a></li>
                                    <li><a href="catalog2.html">Catalog List</a></li>
                                    <li><a href="details1.html">Details Movie</a></li>
                                    <li><a href="details2.html">Details TV Series</a></li>
                                </ul>
                            </li>
                            <!-- end dropdown -->

                            <li class="header__nav-item">
                                <a href="pricing.html" class="header__nav-link">Pricing Plan</a>
                            </li>

                            <li class="header__nav-item">
                                <a href="faq.html" class="header__nav-link">Help</a>
                            </li>

                            <!-- dropdown -->
                            <li class="dropdown header__nav-item">
                                <a class="dropdown-toggle header__nav-link header__nav-link--more" href="#" role="button" id="dropdownMenuMore" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="icon ion-ios-more"></i></a>

                                <ul class="dropdown-menu header__dropdown-menu" aria-labelledby="dropdownMenuMore">
                                    <li><a href="about.html">About</a></li>
                                    <li><a href="profile.html">Profile</a></li>
                                    <li><a href="signin.html">Sign In</a></li>
                                    <li><a href="signup.html">Sign Up</a></li>
                                    <li><a href="404.html">404 Page</a></li>
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
                                    <li><a href="#">Russian</a></li>
                                </ul>
                            </div>
                            <!-- end dropdown -->

                            <a href="signin.html" class="header__sign-in">
                                <i class="icon ion-ios-log-in"></i>
                                <span>sign in</span>
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
                        <span class="card__rate"><i class="icon ion-ios-star"></i>8.4</span>

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
<section>
    <div class="container-fluid">
        <div class="row>">
            <div class="col-12">
                <div class="owl-carousel">
                    <div class="card">
                        <div class="card__cover">
                            <img src="img/covers/cover.jpg" alt="">
                            <a href="#" class="card__play">
                                <i class="icon ion-ios-play"></i>
                            </a>
                        </div>
                        <div class="card__content">
                            <h3 class="card__title"><a href="#">I Dream in Another Language</a></h3>
                            <span class="card__category">
										<a href="#">Action</a>
										<a href="#">Triler</a>
									</span>
                            <span class="card__rate"><i class="icon ion-ios-star"></i>8.4</span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card__cover">
                            <img src="img/covers/cover.jpg" alt="">
                            <a href="#" class="card__play">
                                <i class="icon ion-ios-play"></i>
                            </a>
                        </div>
                        <div class="card__content">
                            <h3 class="card__title"><a href="#">I Dream in Another Language</a></h3>
                            <span class="card__category">
										<a href="#">Action</a>
										<a href="#">Triler</a>
									</span>
                            <span class="card__rate"><i class="icon ion-ios-star"></i>8.4</span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card__cover">
                            <img src="img/covers/cover.jpg" alt="">
                            <a href="#" class="card__play">
                                <i class="icon ion-ios-play"></i>
                            </a>
                        </div>
                        <div class="card__content">
                            <h3 class="card__title"><a href="#">I Dream in Another Language</a></h3>
                            <span class="card__category">
										<a href="#">Action</a>
										<a href="#">Triler</a>
									</span>
                            <span class="card__rate"><i class="icon ion-ios-star"></i>8.4</span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card__cover">
                            <img src="img/covers/cover.jpg" alt="">
                            <a href="#" class="card__play">
                                <i class="icon ion-ios-play"></i>
                            </a>
                        </div>
                        <div class="card__content">
                            <h3 class="card__title"><a href="#">I Dream in Another Language</a></h3>
                            <span class="card__category">
										<a href="#">Action</a>
										<a href="#">Triler</a>
									</span>
                            <span class="card__rate"><i class="icon ion-ios-star"></i>8.4</span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card__cover">
                            <img src="img/covers/cover.jpg" alt="">
                            <a href="#" class="card__play">
                                <i class="icon ion-ios-play"></i>
                            </a>
                        </div>
                        <div class="card__content">
                            <h3 class="card__title"><a href="#">I Dream in Another Language</a></h3>
                            <span class="card__category">
										<a href="#">Action</a>
										<a href="#">Triler</a>
									</span>
                            <span class="card__rate"><i class="icon ion-ios-star"></i>8.4</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <!-- footer list -->
            <div class="col-12 col-md-3">
                <h6 class="footer__title">Download Our App</h6>
                <ul class="footer__app">
                    <li><a href="#"><img src="img/Download_on_the_App_Store_Badge.svg" alt=""></a></li>
                    <li><a href="#"><img src="img/google-play-badge.png" alt=""></a></li>
                </ul>
            </div>
            <!-- end footer list -->

            <!-- footer list -->
            <div class="col-6 col-sm-4 col-md-3">
                <h6 class="footer__title">Resources</h6>
                <ul class="footer__list">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Pricing Plan</a></li>
                    <li><a href="#">Help</a></li>
                </ul>
            </div>
            <!-- end footer list -->

            <!-- footer list -->
            <div class="col-6 col-sm-4 col-md-3">
                <h6 class="footer__title">Legal</h6>
                <ul class="footer__list">
                    <li><a href="#">Terms of Use</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Security</a></li>
                </ul>
            </div>
            <!-- end footer list -->

            <!-- footer list -->
            <div class="col-12 col-sm-4 col-md-3">
                <h6 class="footer__title">Contact</h6>
                <ul class="footer__list">
                    <li><a href="tel:+18002345678">+1 (800) 234-5678</a></li>
                    <li><a href="mailto:support@moviego.com">support@flixgo.com</a></li>
                </ul>
                <ul class="footer__social">
                    <li class="facebook"><a href="#"><i class="icon ion-logo-facebook"></i></a></li>
                    <li class="instagram"><a href="#"><i class="icon ion-logo-instagram"></i></a></li>
                    <li class="twitter"><a href="#"><i class="icon ion-logo-twitter"></i></a></li>
                    <li class="vk"><a href="#"><i class="icon ion-logo-vk"></i></a></li>
                </ul>
            </div>
            <!-- end footer list -->

            <!-- footer copyright -->
            <div class="col-12">
                <div class="footer__copyright">
                    <small>© 2018 FlixGo. Create by <a href="https://themeforest.net/user/dmitryvolkov/portfolio?ref=DmitryVolkov" target="_blank">Dmitry Volkov</a></small>

                    <ul>
                        <li><a href="#">Terms of Use</a></li>
                        <li><a href="#">Privacy Policy</a></li>
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