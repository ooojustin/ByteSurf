<?php
    
    require '../inc/server.php';
    require '../inc/session.php';
    require_subscription();
    
    global $user;
    
    // gradient color ranges
    define('RED', '#9c3636, #dc3c3c');
    define('GREEN', '#32905c, #6adc3c');

    $email = $user['email'];
    $username = $user['username'];
    
    if (!isset($_POST['send_inquiry']))
    	goto skip_send;

    if (strlen($_POST['message']) < 20) {
    	$notification = "Message must be at least 20 characters.";
    	$notification_colors = RED;	
    	goto skip_send;
    }

    switch ($_POST['select_subject']) {
    	case "general":
    		$subject = "General Inquiry";
		case "request":
			$subject = "Movie / Feature Request";
			break;
		case "bug":
			$subject = "Report a Bug";
			break;
		case "abuse":
			$subject = "Report Abuse / DMCA Notice";
			break;
		case "billing":
			$subject = "Billing Problems";
			break;
		case "problem":
			$subject = 'Problem - ';
			if (isset($_GET['t']))
				$subject .= $_GET['t'];
			else
				$subject .= 'Unknown';
			break;
		default:
			$notification = "Please specify a reason/email subject.";
    		$notification_colors = RED;
    		goto skip_send;
    }

    if (strlen($_POST['message']) > 20) {

        $message = "<b>Username:</b> " . $username . "<br>" . PHP_EOL . 
					"<b>Email Address:</b> " . $email . "<br><br>" . PHP_EOL .
					"<b>Message:</b><br>" . PHP_EOL . $_POST['message'];

  		$response = send_email(
  			$subject, // email subject
  			$message, // email contents (html)
  			'mailer@jexflix.com', // sender email
  			'JexFlix - Support Request', // sender name
  			'support@jexflix.com', // receiver email
  			'JexFlix Staff', // receiver name
  			$email, // reply-to email (optional)
  			$username // reply-to name (optional)
  		);

  		$status = $response->statusCode();
    	$sent = $status >= 200 && $status < 300; // HTTP 2xx == SUCCESS

		if ($sent) {
			$notification = "Message Sent Successfully.";
			$notification_colors = GREEN;
		} else {
			$notification = "Failed to send message. Please close and reopen the page.";
        	$notification_colors = RED;
		}
		
    }

    skip_send:
        
    
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
	<? require '../inc/html/header.php' ?>
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

		<? if (isset($notification)) { ?>
	    <div class="register-error" style="width: auto; background: linear-gradient(to right, <?=$notification_colors?>);">
    	    <span class="signin-error-text"><?=$notification?></span>
    	</div>
    	<? } ?>

    	<?
    		$action = 'index.php';
    		if (isset($_GET['t']))
    			$action .= '?t=' . $_GET['t'];
    	?>
    	
			<form action="<?= $action ?>" method="post" class="profile__form">
				<div class="row">
				    
					<div class="col-12 col-lg-12">
						<div class="profile__group">
							<label class="profile__label" for="username">Subject</label>
					<!--		<input id="subject" type="text" name="subject" value="" class="profile__input" readonly> -->
					
					<select name="select_subject" id="select_subject" onchange="subject_changed()" class="minimal">
                        <option value="general">General Inquiry</option>
                        <option value="request">Movie / Feature Request</option>
                        <option value="bug">Report a Bug</option>
                        <option value="abuse">Report Abuse / DMCA Notice</option>
                        <option value="billing">Billing Problems</option>
                        <? if (isset($_GET['t'])) { ?>
                        <option value="problem" selected="selected">Problem - <?= $_GET['t']?></option> 
                        <? } ?>
                    </select>
                    
						</div>
					</div>
					<div class="col-12 col-lg-12">
						<div class="profile__group">
							<label class="profile__label" for="email">Message (HTML)</label>
							<textarea id="message" name="message" class="profile__input" style="height: 200px; padding-top: 12px; padding-left: 10px;" placeholder="Please enter a brief message regarding your inquiry here (min 20 chars). "></textarea>
						</div>
					</div>

					<div class="col-12" align="right">
						<button class="profile__btn" type="submit" name="send_inquiry">Send</button>
					</div>
				</div>
			</form>

			<? if (isset($_GET['q'])) { ?>
				<script>
					var selector = document.getElementById("select_subject");
					var subjects = ["general", "request", "bug", "abuse", "billing"];
					<? if (isset($_GET['t'])) { ?> 
						selector.push("problem"); 
					<? } ?>
					var selected = subjects.indexOf("<?= $_GET['q'] ?>");
					if (selected > -1)
						selector.selectedIndex = selected; // yeet
				</script>
			<? } ?>

		</div>
	</div>
		
	</div>
	
	<!-- footer -->
	<? require '../inc/html/footer.php' ?>
	<!-- end footer -->
</body>
</html>