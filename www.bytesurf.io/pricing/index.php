<?php
    
    require '../inc/server.php';
    require '../inc/session.php';

    require_login();
    global $user;

    if (!isset($_POST['code']))
    	goto skip_redeem;

    $trial_key = $_POST['code'];
    $trial_key_data = get_trial_key($trial_key);

    if (!trial_key_exists($trial_key))
    	msg('Error', 'That trial key does not exist.');
    else if (!is_null($trial_key_data['user']))
    	msg('Error', 'That trial key has already been used.');
    else if ($trial_key_data['owner'] == $user['username'])
    	msg('Error', 'You can\'t redeem your own trial key.');

    // it exists, it was generated for somebody else, and it hasn't been used
    // redeem the key and mark it as used
    $duration = intval($trial_key_data['duration']);
    if ($duration == -1)
   		update_expires($user['username'], -1);
   	else
   		add_subscription_time($user['username'], $duration);

   	$used_key = $db->prepare('UPDATE trial_keys SET user=:user WHERE trial_key=:trial_key');
   	$used_key->bindValue(':user', $user['username']);
   	$used_key->bindValue(':trial_key', $trial_key);
   	$used_key->execute();

   	$user = get_user($user['username']); // update this data so we have new 'expires'
   //	die('<html>Trial key redeemed successfully!<br><b>Your subscription expires:</b> ' . get_subscription_expiration_date() . '</html>');
   	header("location: ../home");

    skip_redeem:
    
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
									<a href="../pricing" class="header__nav-link">Random</a>
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
        <form action="https://jexflix.com/catalog" method="get" class="header__search">
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
						<h2 class="section__title">Pricing</h2>
						<!-- end section title -->

						<!-- breadcrumb -->
						<ul class="breadcrumb">
							<li class="breadcrumb__item"><a href="#">Home</a></li>
							<li class="breadcrumb__item breadcrumb__item--active">Pricing</li>
						</ul>
						<!-- end breadcrumb -->
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- end page title -->

	<!-- content -->
	<div class="content">
		<!-- profile -->
		<div class="profile">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<div class="profile__content">

							<!-- content tabs nav -->
							<ul class="nav nav-tabs content__tabs content__tabs--profile" id="content__tabs" role="tablist">
								<li class="nav-item">
								    <a class="nav-link active" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="false">Subscription</a>	

								
								</li>

								<li class="nav-item">
								    <a class="nav-link" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="true">Redeem a code</a>
								</li>
							</ul>
							<!-- end content tabs nav -->

							<!-- content mobile tabs nav -->
							<div class="content__mobile-tabs content__mobile-tabs--profile" id="content__mobile-tabs">
								<div class="content__mobile-tabs-btn dropdown-toggle" role="navigation" id="mobile-tabs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<input type="button" value="Subscription">
									<span></span>
								</div>

								<div class="content__mobile-tabs-menu dropdown-menu" aria-labelledby="mobile-tabs">
									<ul class="nav nav-tabs" role="tablist">
										<li class="nav-item"><a class="nav-link active" id="1-tab" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true">Subscription</a></li>

										<li class="nav-item"><a class="nav-link" id="2-tab" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="false">Redeem a code</a></li>
									</ul>
								</div>
							</div>
							<!-- end content mobile tabs nav -->

						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- end profile -->

		<div class="container">
			<!-- content tabs -->
			<div class="tab-content" id="myTabContent">
			    <div class="tab-pane fade" id="tab-2" role="tabpanel" aria-labelledby="2-tab">
					<div class="row">
						<!-- details form -->
						<div class="col-6">
							<form action="" method="post" class="profile__form">
								<div class="row">
									<div class="col-12">
										<div class="profile__group">
											<label class="profile__label" for="code">Trial License</label>
											<input id="code" type="text" name="code" class="redeem-input">
										</div>
									</div>
									<div class="col-12">
										<button class="price__btn" type="submit">Redeem</button>
									</div>
								</div>
							</form>
						</div>
						<!-- end details form -->
					</div>
				</div>
				
				<div class="tab-pane fade show active" id="tab-1" role="tabpanel" aria-labelledby="1-tab">
					<div class="row">
						<!-- price -->
						<div class="col-12 col-md-6 col-lg-4">
							<div class="price price--profile">
								<div class="price__item price__item--first"><span>1 Month</span> <span>$8.99</span></div>
								<div class="price__item"><span>30 days</span></div>
								<div class="price__item"><span>Ultra HD</span></div>
								<div class="price__item"><span><del>Trial Code</del></span></div>
								<div class="price__item"><span>Any Device</span></div>
								<div class="price__item"><span>24/7 Support</span></div>
								<a href="purchase.php?plan=1month" class="price__btn">Choose Plan</a>
							</div>
						</div>
						<!-- end price -->

						<!-- price -->
						<div class="col-12 col-md-6 col-lg-4">
							<div class="price price--profile price--premium">
								<div class="price__item price__item--first"><span>3 Months</span> <span>$19.99</span></div>
								<div class="price__item"><span>3 Months</span></div>
								<div class="price__item"><span>Ultra HD</span></div>
								<div class="price__item"><span>1 Trial Code</span></div>
								<div class="price__item"><span>Any Device</span></div>
								<div class="price__item"><span>24/7 Support</span></div>
								<a href="purchase.php?plan=3months" class="price__btn">Choose Plan</a>
							</div>
						</div>
						<!-- end price -->

						<!-- price -->
						<div class="col-12 col-md-6 col-lg-4">
							<div class="price price--profile">
								<div class="price__item price__item--first"><span>Lifetime</span> <span>$49.99</span></div>
								<div class="price__item"><span>Lifetime</span></div>
								<div class="price__item"><span>Ultra HD</span></div>
								<div class="price__item"><span>3 Trial Codes</span></div>
								<div class="price__item"><span>Any Device</span></div>
								<div class="price__item"><span>24/7 Support</span></div>
								<a href="purchase.php?plan=lifetime" class="price__btn">Choose Plan</a>
							</div>
						</div>
						<!-- end price -->
					</div>
				</div>
			</div>
			<!-- end content tabs -->
		</div>
	</div>
	<!-- end content -->



	<!-- features -->
	<section class="section section--dark">
		<div class="container">
			<div class="row">
				<!-- section title -->
				<div class="col-12">
					<h2 class="section__title section__title--no-margin">Our Features</h2>
				</div>
				<!-- end section title -->

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
			</div>
		</div>
	</section>
	<!-- end features -->

	<!-- footer -->
	<footer class="footer">
		<div class="container">
			<div class="row">
				<!-- footer list -->
				<div class="col-6 col-sm-4 col-md-3">
					<h6 class="footer__title">Resources</h6>
					<ul class="footer__list">
						<li><a href="#">About Us</a></li>
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