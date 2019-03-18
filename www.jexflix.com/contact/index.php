<?php
    
    require '../inc/server.php';
    require '../inc/session.php';
    require_subscription();
    
    global $user;
    
    $subject = "General Inquiry";
    $replyto = $user['email'];
    $username = $user['username'];
    $title = "";
    
    switch ($_GET['q']) {
        case "request":
            $subject = "Movie / Feature Request";
            break;
        case "abuse":
            $subject = "Abuse";
            break;
        case "bug":
            $subject = "Bug report";
            break;
        case "problem":
            if (isset($_GET['t'])) $title = $_GET['t']; 
            $subject = "Problem - " . $title;
            break;
        default:
            break;
    }
    
    if (isset($_POST['send_inquiry']) && strlen($_POST['message']) > 20) {
        $log_message = "Username: " . $username . "\r\n" . "Replying To: " . $replyto . "\r\n" . "\r\n" .  $_POST['message'];
		$headers = 'From: '.'mailer@jexflix.com'."\r\n".
					'Reply-To: '. $replyto ."\r\n" .
					'From: Jexflix - Support Request' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
		@mail('support@jexflix.com', $subject, $log_message, $headers);  
		
		$message = "Message Sent Successfully.";
		$style = "background: linear-gradient(to right, #32905c, #6adc3c);";
    }
    else if (strlen($_POST['message']) < 20) {
        $message = "Please enter a message longer than 20 characters.";
        $style = "background: linear-gradient(to right, #9c3636, #dc3c3c);";
    }
    else {
        $message = "Failed to send message. Please close and reopen the page.";
        $style = "background: linear-gradient(to right, #9c3636, #dc3c3c);";
    }
    
    
    
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
	<section class="section section--first section--bg" >
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section__wrap">
						<!-- section title -->
						<h2 class="section__title">Contact</h2>
						<!-- end section title -->

						<!-- breadcrumb -->
						<ul class="breadcrumb">
							<li class="breadcrumb__item"><a href="../home">Home</a></li>
							<li class="breadcrumb__item breadcrumb__item--active">Contact</li>
						</ul>
						<!-- end breadcrumb -->
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- end page title -->


<div class="container" style="padding-top: 50px">
	<div class="row">
		<div class="col-12">
		<? if (isset($_POST['send_inquiry'])) { ?>
	    <div class="register-error" style="width: auto; <?=$style?>">
    	    <span class="signin-error-text"><?=$message?></span>
    	</div>
    	<? } ?>
    	
			<form action="" method="post" class="profile__form">
				<div class="row">
				    
					<div class="col-12 col-lg-12">
						<div class="profile__group">
							<label class="profile__label" for="username">Subject</label>
							<input id="subject" type="text" name="subject" value="<?=$subject?>" class="profile__input" readonly>
						</div>
					</div>

					<div class="col-12 col-lg-12">
						<div class="profile__group">
							<label class="profile__label" for="email">Message (HTML)</label>
							<textarea id="message" name="message" class="profile__input" style="height: 200px; padding-top: 10px" placeholder="Please enter a brief message regarding your inquiry here (min 20 chars). "></textarea>
						</div>
					</div>

					<div class="col-12" align="right">
						<button class="profile__btn" type="submit" name="send_inquiry">Send</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	
	</div>
	
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