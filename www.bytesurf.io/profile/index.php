<?php

    require '../inc/server.php';
    require '../inc/session.php';
    require '../inc/products.php';
    require_login();
   
	global $user, $db;
   
   	// establish general user details
   	$username = $user['username'];
   	$user_id = $user['id'];
   	$email = $user['email'];

   	// establish reseller details
   	$reseller = get_reseller($username);
   	if (!$reseller) {
   		$reseller['selly_email'] = '';
   		$reseller['selly_api_key'] = '';
   		$reseller['balance'] = 0;
   	}
   
   	// update password
   	if(isset($_POST['oldpass']) && isset($_POST['newpass']) && isset($_POST['confirmpass'])) {
		if ($_POST['newpass'] != $_POST['confirmpass']) return;
       	update_password($username, $_POST['oldpass'], $_POST['newpass']);
   	}
   
   	// update profile pic
   	if (isset($_POST['pfp']))
    	update_picture($username, $_POST['pfp']);

    // get referral info
    $referred_users = get_referred_users($username);
    $referred_users_paid = get_referred_users($username, true);
    $affiliate_balance = round($user['affiliate_balance'], 2, PHP_ROUND_HALF_DOWN);

    // update reseller info (NOTE: VALIDATE EMAIL ADDRESS)
    if (isset($_POST['selly_email']) && isset($_POST['selly_api_key'])) {
    	require '../inc/selly/selly.php';
    	$selly = new SellyAPI($_POST['selly_email'], $_POST['selly_api_key']);
    	if ($selly->is_valid()) {
    		update_reseller($username, $_POST['selly_email'], $_POST['selly_api_key']);
    		$reseller['selly_email'] = $_POST['selly_email'];
    		$reseller['selly_api_key'] = $_POST['selly_api_key'];
    	}
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
	<meta name="keywords" content="">
	<meta name="author" content="Peter Pistachio">
	<title>ByteSurf</title>

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

</head>
<body class="body">
	
	<!-- header -->
	<?=require '../inc/html/header.php'?>
	<!-- end header -->

	<!-- page title -->
	<section class="section section--first section--bg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section__wrap">
						<!-- section title -->
						<h2 class="section__title">Profile</h2>
						<!-- end section title -->

						<!-- breadcrumb -->
						<ul class="breadcrumb">
							<li class="breadcrumb__item"><a href="../home">Home</a></li>
							<li class="breadcrumb__item breadcrumb__item--active">Profile</li>
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
									<h3><?=$username?></h3>
									<span>User ID: <?=$user_id?></span>
								</div>
							</div>

							<!-- content tabs nav -->
							<ul class="nav nav-tabs content__tabs content__tabs--profile" id="content__tabs" role="tablist">
								<li class="nav-item">
									<a class="nav-link active" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true">Profile</a>
								</li>

								<li class="nav-item">
									<a class="nav-link" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="false">Subscription</a>
								</li>

								<li class="nav-item">
									<a class="nav-link" data-toggle="tab" href="#tab-3" role="tab" aria-controls="tab-3" aria-selected="false">Affiliates</a>
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
										<li class="nav-item"><a class="nav-link active" id="1-tab" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true">Profile</a></li>

										<li class="nav-item"><a class="nav-link" id="2-tab" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="false">Subscription</a></li>

										<li class="nav-item"><a class="nav-link" id="3-tab" data-toggle="tab" href="#tab-3" role="tab" aria-controls="tab-3" aria-selected="false">Affiliates</a></li>
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
						<!-- details form -->
						<div class="col-12 col-lg-6">
							<form action="" method="post" class="profile__form">
								<div class="row">
									<div class="col-12">
										<h4 class="profile__title">Profile Details</h4>
									</div>

									<div class="col-12">
										<div class="profile__group">
											<label class="profile__label" for="username">Username</label>
											<input id="username" type="text" name="username" class="profile__input" disabled value="<?=$username?>">
										</div>
									</div>

									<div class="col-12">
										<div class="profile__group">
											<label class="profile__label" for="email">Email</label>
											<input id="email" type="text" name="email" class="profile__input" disabled value="<?=$email?>">
										</div>
									</div>

									<div class="col-12">
										<div class="profile__group">
											<label class="profile__label" for="pfp">Profile Picture</label>
											<input id="pfp" type="text" name="pfp" class="profile__input" placeholder="Enter Image URL">
										</div>
									</div>

									<div class="col-12">
										<button class="profile__btn" type="submit" style="width: 100%;">Save</button>
									</div>
								</div>
							</form>
						</div>
						<!-- end details form -->

						<!-- password form -->
						<div class="col-12 col-lg-6">
							<form action="" method="post" class="profile__form">
								<div class="row">
									<div class="col-12">
										<h4 class="profile__title">Change Password</h4>
									</div>

									<div class="col-12">
										<div class="profile__group">
											<label class="profile__label" for="oldpass">Old Password</label>
											<input id="oldpass" type="password" name="oldpass" class="profile__input">
										</div>
									</div>

									<div class="col-12">
										<div class="profile__group">
											<label class="profile__label" for="newpass">New Password</label>
											<input id="newpass" type="password" name="newpass" class="profile__input">
										</div>
									</div>

									<div class="col-12">
										<div class="profile__group">
											<label class="profile__label" for="confirmpass">Confirm New Password</label>
											<input id="confirmpass" type="password" name="confirmpass" class="profile__input">
										</div>
									</div>

									<div class="col-12">
										<button class="profile__btn" type="submit" style="width: 100%;">Change</button>
									</div>
								</div>
							</form>
						</div>
						<!-- end password form -->
                        
                        <!-- 2 factor authentication -->
                        <div class="col-12 col-lg-12">
                            <?
                                $enabled = !is_null($user['2fa']);
                                $btn_two_factor_auth = $enabled ? 'Disable' : 'Enable';                 
                            ?>
							<form action="2fa.php" method="post" class="profile__form">
								<div class="row">

									<div class="col-12">
										<h4 class="profile__title">Two Factor Authentication</h4>
									</div>
                                    
                                    <input type="hidden" name="action" value="<?= strtolower($btn_two_factor_auth) ?>">
                                    
                                    <div class="col-12">
										<button class="profile__btn" type="submit" style="width: 100%;"><?= $btn_two_factor_auth ?></button>
									</div>
                                    
								</div>
							</form>
						</div>
                        <!-- end 2 factor authentication -->
                        
					</div>
				</div>

				<div class="tab-pane fade" id="tab-2" role="tabpanel" aria-labelledby="2-tab">
					<div class="row">

						<!-- subscription and orders -->
						<div class="col-12 col-lg-6">

							<div class="row">

								<div class="col-12">
									<h4 class="profile__title" style="margin-bottom: 10px">Subscription & Orders</h4>
									<h4 class="profile__title"><b>Expires:</b> <?= get_subscription_expiration_date(); ?></h4>
									<?
										$orders = get_orders($username);
										if (!empty($orders)) {
											foreach ($orders as $order) { ?>
												<label class="profile__label"><?= '<b>[' . $order['invoice'] . ']</b> ' . $order['product'] . ' - $' . $order['amount'] ?></label><br>
											<? } 
										} else { ?>
											<label class="profile__label">You do not have any completed orders.</label>
										<? } 
									?>
								</div>
					
							</div>

						</div>
						<!-- end subscription/orders -->

						<!-- trial key stuff -->
						<div class="col-12 col-lg-6">
							<div class="row">

								<div class="col-12">
									<h4 class="profile__title" style="margin-bottom: 15px">Trial Keys</h4>
									<?
										$get_trials = $db->prepare('SELECT * FROM trial_keys WHERE owner=:username AND user IS NULL');
										$get_trials->bindValue(':username', $username);
										$get_trials->execute();
										if ($get_trials->rowCount() > 0) {
											$trials = $get_trials->fetchAll();
											foreach ($trials as $trial) { ?>
												<label class="profile__label"><b><?= $trial['trial_key']; ?></b> - <?
												if ($trial['duration'] == -1)
													echo 'Lifetime';
												else
													echo ($trial['duration'] / 86400) . ' days'; ?></label><br>
											<? }
										} else { ?>
											<label class="profile__label">You do not have any unused trial keys.</label>
										<? } 
									?>
									<br><br>
									<h4 class="profile__title" style="margin-bottom: 15px">Used Trial Keys</h4>
									<?
										$get_trials = $db->prepare('SELECT * FROM trial_keys WHERE owner=:username AND user IS NOT NULL');
										$get_trials->bindValue(':username', $username);
										$get_trials->execute();
										if ($get_trials->rowCount() > 0) {
											$trials = $get_trials->fetchAll();
											foreach ($trials as $trial) { ?>
												<label class="profile__label"><b><?= $trial['trial_key']; ?></b> - <?
												if ($trial['duration'] == -1)
													echo 'Lifetime';
												else
													echo ($trial['duration'] / 86400) . ' days';
												echo ' - Used by ' . $trial['user']; ?></label><br>
											<? }
										} else { ?>
											<label class="profile__label">You do not have any used trial keys.</label>
										<? } 
									?>
								</div>
					
							</div>
						</div>
						<!-- end trial keys -->

					</div>
				</div>

				<div class="tab-pane fade" id="tab-3" role="tabpanel" aria-labelledby="3-tab">
					<div class="row">

						<!-- referral system form -->
						<div class="col-lg-6">
							<form action="" method="post" class="profile__form">
								<div class="row">

									<div class="col-12">
										<h4 class="profile__title">Referral Link</h4>
									</div>

									<div class="col-12">
										<div class="profile__group">
											<label class="profile__label">Get rewarded when users sign up with your link!</label>
											<input id="referral_link" type="text" class="profile__input" value="https://bytesurf.io/register/?r=<?= $username ?>" disabled>
										</div>
									</div>
                                    
                                    <div class="col-12">
										<label class="profile__label">When a user you referred makes a payment for the first time, you get 10% of the sale. Once you've earned $5.00, you can cash out automatically.</label>
									</div>

								</div>
							</form>
						</div>
						<!-- end referral system form -->

						<!-- referral system form -->
						<div class="col-lg-6">
							<form action="" method="post" class="profile__form">
								<div class="row">

									<div class="col-12">
										<h4 class="profile__title">Referral Statistics</h4>
									</div>

									<div class="col-12 col-md-6 col-lg-12 col-xl-6">
										<div class="profile__group">	
											<label class="profile__label"><b>Referred Users:</b> <?= $referred_users ?></label>
											<br>
											<label class="profile__label"><b>Paid Referrals:</b> <?= $referred_users_paid ?></label>
                                            <br>
											<label class="profile__label"><b>Earnings:</b> $<?= $affiliate_balance ?></label>
                                            <a href="https://bytesurf.io/profile/withdraw.php"><button class="profile__btn" type="button" style="width: 100%;">Withdraw</button></a>
										</div>
									</div>

								</div>
							</form>
						</div>
						<!-- end referral system form -->

						<!-- selly info form -->
						<div class="col-12 col-lg-6">
							<form action="" method="post" class="profile__form">
								<div class="row">
									<div class="col-12">
										<h4 class="profile__title">Selly Details</h4>
									</div>

									<div class="col-12 col-md-6 col-lg-12 col-xl-6">
										<div class="profile__group">
											<label class="profile__label" for="username">Email</label>
											<input id="selly_email" type="text" name="selly_email" class="profile__input" placeholder="Selly Email Address" value="<?=$reseller['selly_email']?>">
										</div>
									</div>

									<div class="col-12 col-md-6 col-lg-12 col-xl-6">
										<div class="profile__group">
											<label class="profile__label" for="email">API Key</label>
											<input id="selly_api_key" type="text" name="selly_api_key" class="profile__input" placeholder="Selly API Key" value="<?=$reseller['selly_api_key']?>">
										</div>
									</div>

									<div class="col-12">
										<button class="profile__btn" type="submit" style="width: 100%;">Save</button>
									</div>
								</div>
							</form>
						</div>
						<!-- end selly form -->

						<!-- reseller balance form -->
						<div class="col-12 col-lg-6">
							<form action="https://bytesurf.io/pricing/purchase.php" method="get" class="profile__form" style="padding-bottom: 10px;">
								<div class="row">
									<div class="col-12">
										<h4 class="profile__title">Balance: $<?= round($reseller['balance'], 2); ?></h4>
									</div>

									<input type="hidden" value="reseller" name="plan" />

									<div class="col-12 col-md-6 col-lg-12 col-xl-6">
										<div class="profile__group">	
											<label class="profile__label" for="username">Amount</label>
											<input id="reseller_deposit_amount" type="text" name="amount" class="profile__input" placeholder="Amount (USD)">
											<button class="profile__btn" type="submit" style="width: 100%; margin-top: 30px;">Deposit</button>
										</div>
									</div>

									<div class="col-12 col-md-6 col-lg-12 col-xl-6">
										<div class="profile__group">	

											<label class="profile__label"><b>Reseller Priority</b></label>

											<?
												global $products;
												foreach ($products as $product) {

													if ($product['price'] == 0)
														continue;

													$priority = get_reseller_priority($username, $product['price']);
													if ($priority == -1)
														$priority = 'N/A';
													else
														$priority = '#' . $priority;

											?> 
											<label class="profile__label"><?= $product['name'] . ' - ' . $priority ?></label> 
											<? } ?>
											
										</div>
									</div>
								</div>
							</form>
						</div>
						<!-- end balance form -->

					</div>
				</div>
			</div>
			<!-- end content tabs -->
		</div>
	</div>
	<!-- end content -->

	<!-- footer -->
	<?=require '../inc/html/footer.php'?>
	<!-- end footer -->
</body>
</html>