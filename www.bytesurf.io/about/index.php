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
	<meta name="keywords" content="">
	<meta name="author" content="Peter Pistachio">
	<title>ByteSurf</title>

</head>
<body class="body">
	
	<!-- header -->
	<?= require '../inc/html/header.php' ?>
	<!-- end header -->


	<!-- page title -->
	<section class="section section--first section--bg" >
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section__wrap">
						<!-- section title -->
						<h2 class="section__title">About Us</h2>
						<!-- end section title -->

						<!-- breadcrumb -->
						<ul class="breadcrumb">
							<li class="breadcrumb__item"><a href="../home">Home</a></li>
							<li class="breadcrumb__item breadcrumb__item--active">About Us</li>
						</ul>
						<!-- end breadcrumb -->
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- end page title -->

	<!-- about -->
	<section class="section">
		<div class="container">
			<div class="row">
				<!-- section title -->
				<div class="col-12">
					<h2 class="section__title"><b>ByteSurf</b> – entertainment for all</h2>
				</div>
				<!-- end section title -->

				<!-- section text -->
				<div class="col-12">
					<p class="section__text">My name is <a href="https://i.imgur.com/gEZ5bko.jpg" target="_blank">Peter Pistachio</a>, I am a 34 year old fullstack developer from Stamford, CT. Ever since I was a young user of the internet, I had the issue of low quality movies buffering, and my ISP sending torrent notices to my email. This is why I decided to create Bytesurf, a fast, legal, high definition movie streaming site.
					<br>
					<br>
					With ByteSurf, you can access your favorite content from your computer, phone, smart tv, or virtually anything connected to the internet with a web browser.
					 </p>

	
				</div>
				<!-- end section text -->

				<!-- feature -->
				<div class="col-12 col-md-6 col-lg-4">
					<div class="feature">
						<i class="icon ion-ios-tv feature__icon"></i>
						<h3 class="feature__title">HD Content</h3>
						<p class="feature__text">Most of our content is viewable in 1080p and 720p. If the option is not there, we can guarentee it is not available anywhere else on the internet.</p>
					</div>
				</div>
				<!-- end feature -->

				<!-- feature -->
				<div class="col-12 col-md-6 col-lg-4">
					<div class="feature">
						<i class="icon ion-ios-trophy feature__icon"></i>
						<h3 class="feature__title">Premium Support</h3>
						<p class="feature__text">With our highly trained team of support members, if there's something missing or a problem you can contact us and we will get back to you within a day!</p>
					</div>
				</div>
				<!-- end feature -->
				<!-- feature -->
				<div class="col-12 col-md-6 col-lg-4">
					<div class="feature">
						<i class="icon ion-ios-rocket feature__icon"></i>
						<h3 class="feature__title">Fast</h3>
						<p class="feature__text">Our premium streaming servers make sure your content can be delivered in high definition, without that annoying buffering.</p>
					</div>
				</div>
				<!-- end feature -->

				<!-- feature -->
				<div class="col-12 col-md-6 col-lg-4">
					<div class="feature">
						<i class="icon ion-ios-globe feature__icon"></i>
						<h3 class="feature__title">Subtitles For All</h3>
						<p class="feature__text">We try our absolute best to make sure subtitles are available for common languages. We don't want anyone left out.</p>
					</div>
				</div>
				<!-- end feature -->

				<!-- feature -->
				<div class="col-12 col-md-6 col-lg-4">
					<div class="feature">
						<i class="icon ion-ios-bulb feature__icon"></i>
						<h3 class="feature__title">New Releases</h3>
						<p class="feature__text">New movies and shows are released quickly after they release. Don't miss out on new content because you don't want to go to a theater.</p>
					</div>
				</div>
				<!-- end feature -->

				<!-- feature -->
				<div class="col-12 col-md-6 col-lg-4">
					<div class="feature">
						<i class="icon ion-ios-cash feature__icon"></i>
						<h3 class="feature__title">Competitive Pricing</h3>
						<p class="feature__text">We charge less than our competitors, and our main goal is to cover server costs. We're one of the only providers with a lifetime payment plan!</p>
					</div>
				</div>
				<!-- end feature -->

			</div>
		</div>
	</section>
	<!-- end about -->

	<!-- how it works -->
	<section class="section section--dark">
		<div class="container">
			<div class="row">
				<!-- section title -->
				<div class="col-12">
					<h2 class="section__title section__title--no-margin">How it works?</h2>
				</div>
				<!-- end section title -->

				<!-- how box -->
				<div class="col-12 col-md-6 col-lg-4">
					<div class="how">
						<span class="how__number">01</span>
						<h3 class="how__title">Create an account</h3>
						<p class="how__text">At the moment, registration is open to all, but not for long. All it takes is an email and password so hurry and get your spot!</p>
					</div>
				</div>
				<!-- ebd how box -->

				<!-- how box -->
				<div class="col-12 col-md-6 col-lg-4">
					<div class="how">
						<span class="how__number">02</span>
						<h3 class="how__title">Choose your Plan</h3>
						<p class="how__text">With multiple subscription plans, you can choose what works with your schedule, financial situation, or whatever the case may be.</p>
					</div>
				</div>
				<!-- ebd how box -->

				<!-- how box -->
				<div class="col-12 col-md-6 col-lg-4">
					<div class="how">
						<span class="how__number">03</span>
						<h3 class="how__title">Enjoy</h3>
						<p class="how__text">Sit back, invite your family and friends, and enjoy watching your favorite content at ease.</p>
					</div>
				</div>
				<!-- ebd how box -->
			</div>
		</div>
	</section>
	<!-- end how it works -->


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