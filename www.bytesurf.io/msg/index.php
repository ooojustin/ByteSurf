<?php

    session_start();
    
    if (!isset($_SESSION['msg']))
    	header("location: ../404");

    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);

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

	<!-- page 404 -->
	<div class="page-404 section--bg" data-bg="../img/section/section.jpg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="page-404__wrap" style="padding: 0px">
						<div class="page-404__content" style="max-width: 700px;">						
							<h1 class="page-404__title" style="font-size: 35px">
								<?= $msg['title'] ?>				
							</h1>
							<p class="page-404__text">
								<?= $msg['message'] ?>
							</p>
                            <? if (is_null($msg['btn_link'])) { ?>
							<button onclick="history.go(-1);" class="page-404__btn"><?= $msg['btn_text']; ?></button>
                            <? } else { ?>
                            <a href="<?= $msg['btn_link']; ?>"><button class="page-404__btn"><?= $msg['btn_text']; ?></button></a>
                            <? } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end page 404 -->

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