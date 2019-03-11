<?php

    require '../inc/server.php';
    require '../inc/session.php';
    require_administrator();

    require 'statistics.php';
   
	global $user;
	$username = $user['username'];
	$role = intval($user['role']);

	// variable determining how many days back to show income (default: 7)
	$days = 7;
	if (isset($_GET['days']) && is_numeric($_GET['days']))
		$days = intval($_GET['days']);

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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.bundle.min.js" integrity="sha256-MZo5XY1Ah7Z2Aui4/alkfeiq3CopMdV/bbkc/Sh41+s=" crossorigin="anonymous"></script>

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
	<section class="section section--first section--bg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section__wrap">
						<!-- section title -->
						<h2 class="section__title">Administration</h2>
						<!-- end section title -->

						<!-- breadcrumb -->
						<ul class="breadcrumb">
							<li class="breadcrumb__item"><a href="../home">Home</a></li>
							<li class="breadcrumb__item"><a href="../profile">Profile</a></li>
							<li class="breadcrumb__item breadcrumb__item--active">Admin</li>
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
							<div class="profile__user">
								<div class="profile__avatar">
								    <? if (isset($user['pfp'])) { ?>
									<img src=<?= '"' . $user['pfp'] . '"' ?> alt="">
									<? } ?>
								</div>
								<div class="profile__meta">
									<h3><?= $username ?></h3>
									<span>Role: <?= $role ?></span>
								</div>
							</div>

							<!-- content tabs nav -->
							<ul class="nav nav-tabs content__tabs content__tabs--profile" id="content__tabs" role="tablist">
								<li class="nav-item">
									<a class="nav-link active" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true">Statistics</a>
								</li>

								<li class="nav-item">
									<a class="nav-link" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="false">Mailer</a>
								</li>
							</ul>
							<!-- end content tabs nav -->

							<!-- content mobile tabs nav -->
							<div class="content__mobile-tabs content__mobile-tabs--profile" id="content__mobile-tabs">
								<div class="content__mobile-tabs-btn dropdown-toggle" role="navigation" id="mobile-tabs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<input type="button" value="Profile">
									<span></span>
								</div>

								<div class="content__mobile-tabs-menu dropdown-menu" aria-labelledby="mobile-tabs">
									<ul class="nav nav-tabs" role="tablist">
										<li class="nav-item"><a class="nav-link active" id="1-tab" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true">Statistics</a></li>

										<li class="nav-item"><a class="nav-link" id="2-tab" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="false">Mailer</a></li>
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

				<div class="tab-pane fade show active" id="tab-1" role="tabpanel" aria-labelledby="1-tab">
					<div class="row">


						<!-- general info -->
						<div class="col-12 col-lg-6">
							<div class="row">
								<div class="col-12">
									<!---<h4 class="profile__title" style="margin-bottom: 15px">more info here</h4>--->
								</div>
							</div>
						</div>
						<!-- end general info -->

						<!-- graph code -->
						<canvas id="incomeChart" width="400" height="120" style="margin-bottom: 30px;"></canvas>
						<canvas id="loginChart" width="400" height="120" style="margin-bottom: 30px;"></canvas>
						<canvas id="resellerIncomeChart" width="400" height="120" style="margin-bottom: 30px;"></canvas>

						<script>

							Chart.defaults.global.defaultFontColor = 'white';
							Chart.defaults.global.defaultFontFamily = "'Ubuntu', sans-serif";

							var incomeChartCFG = {
								type: 'line',
								data: {
									labels: [<? output_data('day_desc', $days, true); ?>],
									datasets: [{
										label: 'Direct Sales',
										backgroundColor: 'rgb(0, 255, 255)',
										borderColor: 'rgb(0, 255, 255)',
										data: [<? output_data('get_direct_sales_days_ago', $days); ?>],
										fill: false,
									}, {
										label: 'Reseller Deposits',
										fill: false,
										backgroundColor: 'rgb(239, 45, 220)',
										borderColor: 'rgb(239, 45, 220)',
										data: [<? output_data('get_reseller_deposits_days_ago', $days); ?>],
									}, {
										label: 'Total Income',
										fill: false,
										backgroundColor: 'rgb(66, 244, 72)',
										borderColor: 'rgb(66, 244, 72)',
										data: [<? output_data('get_total_days_ago', $days); ?>],
									}]
								},
								options: {
									responsive: true,
									title: {
										display: true,
										text: 'Daily Income'
									},
									tooltips: {
										mode: 'index',
										intersect: false,
									},
									hover: {
										mode: 'nearest',
										intersect: true
									},
									scales: {
										xAxes: [{
										display: true,
										scaleLabel: {
											display: true,
											labelString: 'Day of the Month'
										}
									}],
										yAxes: [{
											display: true,
											scaleLabel: {
												display: true,
												labelString: 'Amount (USD)'
											}
										}]
									}
								}
							};

							var loginChartCFG = {
								type: 'line',
								data: {
									labels: [<? output_data('day_desc', $days, true); ?>],
									datasets: [{
										label: 'Logins',
										backgroundColor: 'rgb(232, 244, 66)',
										borderColor: 'rgb(232, 244, 66)',
										data: [<? output_data('get_logins', $days); ?>],
										fill: false,
									}, {
										label: 'Registrations',
										backgroundColor: 'rgb(39, 23, 216)',
										borderColor: 'rgb(39, 23, 216)',
										data: [<? output_data('get_registrations', $days); ?>],
										fill: false,
									}]
								},
								options: {
									responsive: true,
									title: {
										display: true,
										text: 'Reseller Sales'
									},
									tooltips: {
										mode: 'index',
										intersect: false,
									},
									hover: {
										mode: 'nearest',
										intersect: true
									},
									scales: {
										xAxes: [{
										display: true,
										scaleLabel: {
											display: true,
											labelString: 'Day of the Month'
										}
									}],
										yAxes: [{
											display: true,
											scaleLabel: {
												display: true,
												labelString: 'Amount (USD)'
											}
										}]
									}
								}
							};

							var resllerIncomeChartCFG = {
								type: 'line',
								data: {
									labels: [<? output_data('day_desc', $days, true); ?>],
									datasets: [{
										label: 'PayPal Sales',
										backgroundColor: 'rgb(255, 0, 0)',
										borderColor: 'rgb(255, 0, 0)',
										data: [<? output_data('get_reseller_sales', $days); ?>],
										fill: false,
									}]
								},
								options: {
									responsive: true,
									title: {
										display: true,
										text: 'Reseller Sales'
									},
									tooltips: {
										mode: 'index',
										intersect: false,
									},
									hover: {
										mode: 'nearest',
										intersect: true
									},
									scales: {
										xAxes: [{
										display: true,
										scaleLabel: {
											display: true,
											labelString: 'Day of the Month'
										}
									}],
										yAxes: [{
											display: true,
											scaleLabel: {
												display: true,
												labelString: 'Amount (USD)'
											}
										}]
									}
								}
							};

							window.onload = function() {

								var incomeChartCTX = document.getElementById('incomeChart').getContext('2d');
								window.myLine = new Chart(incomeChartCTX, incomeChartCFG);

								var loginChartCTX = document.getElementById('loginChart').getContext('2d');
								window.myLine = new Chart(loginChartCTX, loginChartCFG);

								var resellerIncomeChartCTX = document.getElementById('resellerIncomeChart').getContext('2d');
								window.myLine = new Chart(resellerIncomeChartCTX, resllerIncomeChartCFG);

							};

						</script>
						<!-- end graph code -->

					</div>
				</div>

				<!-- Mailer Tab -->
				<div class="tab-pane fade" id="tab-2" role="tabpanel" aria-labelledby="2-tab">

					<div class="row">
						<div class="col-12">
							<form action="mailer.php" method="post" class="profile__form">
								<div class="row">
									<div class="col-12">
										<h4 class="profile__title">Mass Mailer</h4>
									</div>

									<div class="col-12 col-lg-12">
										<div class="profile__group">
											<label class="profile__label" for="username">Email List (URL)</label>
											<input id="email_list" type="text" name="email_list" class="profile__input">
										</div>
									</div>

									<div class="col-12 col-lg-12">
										<div class="profile__group">
											<label class="profile__label" for="username">Subject</label>
											<input id="subject" type="text" name="subject" class="profile__input">
										</div>
									</div>

									<div class="col-12 col-lg-12">
										<div class="profile__group">
											<label class="profile__label" for="email">Message (HTML)</label>
											<textarea id="message" name="message" class="profile__input" style="height: 200px"></textarea>
										</div>
									</div>

									<div class="col-12" align="right">
										<button class="profile__btn" type="submit">Send</button>
									</div>
								</div>
							</form>
						</div>
					</div>

					<div class="row">
						<div class="col-12">
							<!-- html preview or something? -->
						</div>
					</div>

				</div>
				<!-- End Mailer Tab -->

			</div>
			<!-- end content tabs -->
		</div>
	</div>
	<!-- end content -->

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
</body>
</html>