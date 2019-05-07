<?php
    require '../inc/server.php';
    require '../inc/session.php';
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
	<meta name="author" content="Sam Soy">
	<title>Bytesurf</title>
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
									<a href="../pricing" class="header__nav-link">Pricing Plan</a>
								</li>
								<li class="header__nav-item">
									<a href="../faq" class="header__nav-link">Help</a>
								</li>
								<!-- dropdown -->
								<li class="dropdown header__nav-item">
									<a class="dropdown-toggle header__nav-link header__nav-link--more" href="#" role="button" id="dropdownMenuMore" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="icon ion-ios-more"></i></a>
									<ul class="dropdown-menu header__dropdown-menu" aria-labelledby="dropdownMenuMore">
										<li><a href="../about">About</a></li>
										<li><a href="../profile">Profile</a></li>
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
									</ul>
								</div>
								<!-- end dropdown -->
								<a href="index.php?logout=1" class="header__sign-in">
									<i class="icon ion-ios-log-in"></i>
									<span>Sign Out</span>
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
        <form action="https://bytesurf.io/catalog" method="get" class="header__search">
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
	<!-- page title -->
	<section class="section section--first section--bg" data-bg="img/section/section.jpg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section__wrap">
						<!-- section title -->
						<h2 class="section__title">FAQ</h2>
						<!-- end section title -->
						<!-- breadcrumb -->
						<ul class="breadcrumb">
							<li class="breadcrumb__item"><a href="../home">Home</a></li>
							<li class="breadcrumb__item breadcrumb__item--active">FAQ</li>
						</ul>
						<!-- end breadcrumb -->
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- end page title -->
	<!-- faq -->
	<section class="section">
		<div class="container">
			<div class="row">
				<div class="col-12 col-md-6">
			
					<div class="faq">
						<h3 class="faq__title">What is bytesurf?</h3>
						<p class="faq__text">Bytesurf was created by me when I had the issue of low quality movie buffering and my ISP sending torrent notices to my email. I had the idea in mind to create a fast legal, high definition movie streaming site for everyone to use at their leisure.</p>
					</div>

					<div class="faq">		
						<h3 class="faq__title">What payment methods do you support?</h3>
						<p class="faq__text">Currently we are accepting payments via Paypal and Bitcoin</p>
					</div>
					<div class="faq">
						<h3 class="faq__title">Why isn't there a HD version of this video?</h3>
						<p class="faq__text">If there isn't a HD version of this video, it is very unlikely you will find a HD version anywhere else on the internet.</p>
					</div>
					<div class="faq">
						<h3 class="faq__title">Are your payments secure?</h3>
						<p class="faq__text">Our payments are processed by 3rd party API's with known reputable history, coinpayments and selly.</p>
					</div>

					<div class="faq">
						<h3 class="faq__title">I forgot my password.</h3>
						<p class="faq__text">You can make a password reset <a href="https://bytesurf.io/login/reset/">here</a></p>
					</div>

				</div>

				<div class="col-12 col-md-6">
			
					<div class="faq">
						<h3 class="faq__title">What Browsers are supported?</h3>
						<p class="faq__text">Bytesurf should run on nearly all browsers including mobile devices as long as they support Javascript</p>
					</div>

					<div class="faq">
						<h3 class="faq__title">How do you handle my privacy?</h3>
						<p class="faq__text">Your privacy matters to us. We handle your information in a secure way that even we don't know what your information is, rest can be assured that everything is safely hashed and encrypted in our databases.</p>
					</div>

					<div class="faq">
						<h3 class="faq__title">How can I contact you?</h3>
						<p class="faq__text">You can come talk to us via <a href="https://discordapp.com/invite/A63TFhP">Discord</a>, send us an email at: support@bytesurf.io or even a support ticket <a href="https://bytesurf.io/contact">here</a></p>
					</div>

				</div>

			</div>

		</div>

	</section>

	<!-- end faq -->



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

						<li><a href="mailto:support@bytesurf.io">support@bytesurf.io</a></li>

					</ul>

				</div>

				<!-- end footer list -->



				<!-- footer copyright -->

				<div class="col-12">

					<div class="footer__copyright">

						<small class="section__text">© 2019 bytesurf. Created by <a href="https://i.imgur.com/gEZ5bko.jpg" target="_blank">Peter Pistachio</a></small>



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