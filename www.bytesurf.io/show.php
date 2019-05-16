<?php
	require 'inc/server.php';
	require 'inc/session.php';
	require 'inc/imdb.php';
	require_subscription();

	date_default_timezone_set('UTC'); // why?

	global $user;

	if (!isset($_GET['t']))
		msg('Error', 'Show not specified.');

	$data = get_series_data($_GET['t']);
	if (!$data)
		msg('Error', 'Show not found.');

	$title = $data['title'];
	$description = $data['description'];
	$thumbnail = authenticate_cdn_url($data['thumbnail']);
	$preview = authenticate_cdn_url($data['preview']);
	$year = $data['year'];
	$certification = $data['certification'];
	$rating = $data['rating'];
	$series_data_url = authenticate_cdn_url($data['data'], true);
	$series_data_raw = file_get_contents($series_data_url); // note: using 'get_request' instead of 'file_get_contents'
	$series_data = json_decode($series_data_raw, true);
	$genres = json_decode($data['genres']);
	$show_url_built = "https://bytesurf.io/show.php?t=" . $_GET['t'];

	// determine whether or not an episode was specified and set default s/e vars
	$is_specific_episode = isset($_GET['s']) && isset($_GET['e']);
	default_param('s', 1);
	default_param('e', 1);

	// download episode data json
	$url = "https://cdn.bytesurf.io/shows/" . $data['url'] . "/" . $_GET['s'] . "/" . $_GET['e'] . "/";
	$specific_data_url = "https://cdn.bytesurf.io/shows/" . $data['url'] . "/" . $_GET['s'] . "/" . $_GET['e'] . "/";
	$specific_data_url = authenticate_cdn_url($specific_data_url . 'data.json', true);
	$specific_data_raw = get_request($specific_data_url);
	$specific_data = json_decode($specific_data_raw, true);

	// if s/e were set, adjust title and description to be specific to episode instead of series
	if ($is_specific_episode) {
		$title = $title . " - " . $specific_data['title'];
		$description = $specific_data['description'];
	}

	// get episode sub files
	if (array_key_exists('subs', $specific_data)) {
		$subs = $specific_data['subs'];
		if (count($subs) > 0)
			$subs[0]['default'] = 'true';
	} else
		$subs = array();

	// default 'watched' button text/value
	$watched = is_watched();
	$watched_btn_text = $watched ? 'REMOVE FROM WATCHED' : 'ADD TO WATCHED';
	$watched_btn_value = $watched ? 'remove_from_watched' : 'add_to_watched';

	// get user party
	$party = get_active_party();

?>
<!DOCTYPE html>
<html lang="en">

<head>
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
	<script src="js/pako.min-1.0.10.js"></script>
	<script src="js/main.js"></script>
	<script src="js/progress.tracker.js"></script>

	<!-- PARTY SYSTEM -->
	<? initialize_party_system(); ?>
	<!-- END PARTY SYSTEM -->

	<!-- Favicons -->
	<link rel="icon" type="image/png" href="../icon/favicon-32x32.png" sizes="32x32">
	<link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">

	<meta name="description" content="">
	<meta name="keywords" content="">A
	<meta name="author" content="Peter Pistachio">
	<title>ByteSurf</title>

	<!-- chat js -->
	<script>
		var Globals = new(function() {
			this.Opened = true;
		})()

		function Utils() {}
		Utils.prototype = {
			constructor: Utils,
			isElementInView: function(element, fullyInView) {
				var pageTop = $(window).scrollTop();
				var pageBottom = pageTop + $(window).height();
				var elementTop = $(element).offset().top;
				var elementBottom = elementTop + $(element).height();

				if (fullyInView === true) {
					return ((pageTop < elementTop) && (pageBottom > elementBottom));
				} else {
					return ((elementTop <= pageBottom) && (elementBottom >= pageTop));
				}
			}
		};
		var Utils = new Utils();
	</script>
	<!-- end chat js -->
</head>

<body class="body">
	<div style="width: 100%; overflow: hidden;">

		<div id="left_section">

			<!-- header -->
			<? require 'inc/html/header.php' ?>
			<!-- end header -->

			<!-- party dialog -->
			<? $party ? require 'inc/html/party_modal.php' : ''; ?>
			<!-- end party dialog -->

			<!-- toggle chat btn -->
			<?php if ($party) { ?>
				<span id="span_chat" style="float: right; position: fixed; top: 50%; right: 27%; z-index: 5;">
					<button id="chatbtn" class="filter__btn" name="chatbtn" type="button" style="font-size: 30px; height: 65px; width: 40px;"><i class="fas fa-arrow-right"></i></button>
				</span>
			<?php } ?>
			<!-- end toggle chat btn -->

			<!-- details -->
			<section class="section details">
				<!-- details background -->
				<div class="details__bg" data-bg="<?= $preview ?>"></div>
				<!-- end details background -->

				<!-- details content -->
				<div class="container">
					<div class="row">
						<!-- title -->
						<div class="col-12">
							<h1 class="details__title"><?= $title ?></h1>
						</div>
						<!-- end title -->

						<!-- content -->
						<div class="col-10">
							<div class="card card--details card--series">
								<div class="row">
									<!-- card cover -->
									<div class="col-12 col-sm-4 col-md-4 col-lg-3 col-xl-3">
										<div class="card__cover">
											<img src="<?= $thumbnail ?>" alt="">
										</div>
									</div>
									<!-- end card cover -->

									<!-- card content -->
									<div class="col-12 col-sm-8 col-md-8 col-lg-9 col-xl-9">
										<div class="card__content">
											<div class="card__wrap">
												<span class="card__rate"><i class="icon ion-ios-star"></i><?= $rating ?></span>

												<ul class="card__list">
													<li>HD</li>
													<li><?= $certification ?></li>
												</ul>
											</div>

											<ul class="card__meta">
												<li><span>Genre:</span>
													<? foreach ($genres as $genre) { ?>
														<a href="#"><?= ucwords($genre) ?></a>
													<? } ?>
												</li>
												<li><span>Release year: </span><?= $year ?></li>
												<li><span>View on: </span><a href="https://www.imdb.com/title/<?= $data['imdb_id'] ?>" target="blank">IMDB</a></li>
											</ul>

											<div class="card__description card__description--details">
												<?= $description ?>
											</div>
										</div>
									</div>
									<!-- end card content -->
								</div>
							</div>
						</div>
						<!-- end content -->

						<!-- player -->
						<div class="col-12">
							<video controls crossorigin playsinline poster="<?= $preview ?>" id="player">
								<!-- Video files -->

								<? foreach ($specific_data['qualities'] as $quality) { ?>
									<source src="<?= authenticate_cdn_url($url . $quality['resolution'] . ".mp4") ?>" type="video/mp4" size="<?= $quality['resolution'] ?>" />
								<? } ?>

								<!-- Caption files -->
								<?
								foreach ($subs as $sub) {
									$sub_end = isset($sub['default']) ? ' default' : '';
									?>
									<track kind="captions" label="<?= $sub['language'] ?>" srclang="<?= $sub['language'] ?>" src="<?= authenticate_cdn_url('https://cdn.bytesurf.io/' . $sub['url']) ?>" <?= $sub_end ?>>
								<? } ?>

							</video>

							<!-- party btn -->
							<?
							$party_btn_action = $party ? 'OPEN' : 'CREATE';
							$party_btn_link = $party ? '#' : 'https://bytesurf.io/party.php?action=create';
							$party_a_onclick = $party ? 'return false;' : '';
							?>
							<span style="float: left; padding-top: 10px; padding-bottom: 10px;">
								<a href="<?= $party_btn_link ?>" onclick="<?= $party_a_onclick ?>">
									<button class="filter__btn" id="party-modal-btn" type="button" style="font-size: 10px; height: 35px; width: 170px;"><?= $party_btn_action ?> PARTY</button>
								</a>
							</span>
							<? if ($party) { ?>
								<script>
									initialize_modal_box('party-modal', 'party-modal-btn');
								</script>
							<? } ?>
							<!-- end party btn -->

							<!-- watched btn -->
							<span style="float: right; padding-top: 10px; padding-bottom: 20px;">
								<button onclick="toggle_watched(this)" class="filter__btn" name="watchbtn" value="<?= $watched_btn_value ?>" type="button" style="font-size: 10px; height: 35px; width: 170px;"><?= $watched_btn_text ?></button>
							</span>
							<!-- end watched btn -->

						</div>
						<!-- end player -->

						<!-- accordion -->
						<div class="col-12 col-lg-6" style="max-width: 100%; flex: 100%">
							<div class="accordion" id="accordion">
								<div class="accordion__card">

									<? foreach ($series_data['seasons'] as $seasons) {

										$season_data_url = "https://cdn.bytesurf.io/shows/" . $data['url'] . "/" . $seasons['season'] . "/data.json";
										$season_data = json_decode(file_get_contents(authenticate_cdn_url($season_data_url, true)), true);
										?>



										<div class="card-header" id="heading<?= $seasons['season'] ?>">
											<button type="button" data-toggle="collapse" data-target="#collapse<?= $seasons['season'] ?>" aria-expanded="false" aria-controls="collapse<?= $seasons['season'] ?>">
												<span><?= $seasons['title'] ?></span>
												<span><?= count($season_data['episodes']) ?> Episodes from <?= date("F, Y", time() - $season_data['episodes'][0]['released']) ?> until <?= date("F, Y", time() - $season_data['episodes'][count($season_data['episodes']) - 1]['released']) ?></span>
											</button>
										</div>

										<div id="collapse<?= $seasons['season'] ?>" class="collapse hide" aria-labelledby="heading<?= $seasons['season'] ?>" data-parent="#accordion">
											<div class="card-body">
												<table class="accordion__list">
													<thead>
														<tr>
															<th>#</th>
															<th>Title</th>
															<th>Air Date</th>
														</tr>
													</thead>

													<tbody>

														<? foreach ($season_data['episodes'] as $episodes) { ?>
															<tr>
																<? $url = $show_url_built . '&s=' . $seasons['season'] . '&e=' . $episodes['episode']; ?>
																<th><a href="<?= $url ?>" style="color: rgba(255,255,255,0.7); display: block;"><?= $episodes['episode'] ?><a></th>
																<td><a href="<?= $url ?>" style="color: rgba(255,255,255,0.7); display: block;"><?= $episodes['title'] ?></a></td>
																<td><a href="<?= $url ?>" style="color: rgba(255,255,255,0.7); display: block;"><?= date("F j, Y", time() - $episodes['released']) ?></a></td>
															</tr>
															</a>
														<? } ?>


													</tbody>
												</table>
											</div>
										</div>

									<? } ?>
								</div>
							</div>
						</div>
						<!-- end accordion -->


					</div>
				</div>
				<!-- end details content -->
			</section>
			<!-- end details -->
		</div>


		<style>
			.header {
				height: 90px;
			}
		</style>


		<? if ($party) { ?>
			<!-- party chat -->
			<script>
				function send_party_message() {
					console.log('sent');
					let message_textbox = document.getElementById('party_chat_message');
					send_party_chat_message(message_textbox.value);
					message_textbox.value = '';
				}

				function toggle_chat() {

					var left_element = document.getElementById("left_section");

					left_element.style.maxWidth = Globals.Opened ? "100%" : "70%";
					left_element.style.width = Globals.Opened ? "100%" : "70%";
					left_element.style.cssFloat = Globals.Opened ? "none" : "left";



					if (Globals.Opened)
						document.getElementById("span_chat").style.right = "0px";

					document.getElementById("chatbtn").innerHTML = "<i class=\"fas fa-arrow-" + (Globals.Opened ? "left" : "right") + "\"></i>";
					document.getElementById("chatroom_input_row").style.position = Globals.Opened ? "relative" : "fixed";
					document.getElementById("chatroom_row").style.position = Globals.Opened ? "relative" : "fixed";
					document.getElementById("right_col_el").style.display = Globals.Opened ? "none" : "block";


					Globals.Opened = !Globals.Opened;

				}

				function set_chat_elements() {

					if (!Globals.Opened)
						return;

					var footer_element = document.getElementById("footer");
					var col_element = document.getElementById("col_style");
					var left_section_element_style = document.getElementById("left_section").style;
					left_section_element_style.width = "70%";
					left_section_element_style.cssFloat = "left";
					left_section_element_style.maxWidth = "70%";
					var can_see = Utils.isElementInView(footer_element, false);

					var pageTop = $(window).scrollTop();
					var pageBottom = pageTop + $(window).height();
					var chatroom_row = document.getElementById("chatroom_row");

					var elementTop = $(chatroom_row).offset().top;
					var elementWidth = $(chatroom_row).width();
					var elementBottom = elementTop + $(chatroom_row).height();
					var footer_top = $(footer_element).offset().top;
					var chat_span_el = document.getElementById("span_chat");
					var chat_input_row_el = document.getElementById("chatroom_input_row");

					var calculated_len = (can_see ? footer_top : pageBottom) - elementTop - 1 - 60;

					chatroom_row.style.position = can_see ? "absolute" : "fixed";
					chatroom_row.style.top = can_see ? (90 + pageTop).toString() + "px" : "90px";
					chatroom_row.style.height = calculated_len.toString() + "px";
					chatroom_row.style.maxHeight = calculated_len.toString() + "px";
					col_element.style.maxHeight = calculated_len.toString() + "px";
					chat_span_el.style.top = ((can_see ? calculated_len : pageBottom - elementTop) / 2 + 90).toString() + "px";
					chat_input_row_el.style.top = calculated_len.toString() + "px";
					chat_span_el.style.right = (elementWidth - 15).toString() + "px";

				}

				function set_timeoutfunction() {
					var loop = setInterval(set_chat_elements, 100);
				}

				$(document).ready(function() {

					document.getElementById("chatbtn").addEventListener("click", toggle_chat);
					document.getElementById("chatbtn").addEventListener("click", set_chat_elements);
					window.addEventListener("scroll", set_chat_elements, false);
					window.addEventListener("resize", set_chat_elements, false);
					window.addEventListener("load", set_chat_elements, false);
					window.onload = set_timeoutfunction;

					document.getElementById("party_chat_send").addEventListener("click", send_party_message);
					document.getElementById("party_chat_message").addEventListener("keyup", function(event) {
						if (event.keyCode === 13) {
							event.preventDefault();
							document.getElementById("party_chat_send").click();
						}
					});

				});
			</script>
			<div id="right_col_el" style="margin-left: 70%;">
				<div class="container">
					<!-- chat input-->
					<div class="row" id="chatroom_input_row" style="height: 1086px; position: fixed; max-width: 100%; width: 30%; right: 0px; margin-top: 90px; max-height: 1086px; margin-left: 0px; margin-right: 0px;">
						<div class="col-12" style="padding-right: 0px;">
							<div class="header__search-content">
								<input type="text" id="party_chat_message" placeholder="Send a message...">
								<button type="submit" id="party_chat_send">SEND</button>
							</div>
						</div>
					</div>
					<!-- end chat input -->
					<div class="row" id="chatroom_row" style="height: 100%; position: fixed; max-width: 100%; width: 30%; right: 0px; top: 90px; margin-left: 0px; margin-right: 0px;">
						<div class="col-12 col-lg-6" id="col_style" style="max-width: 100%;flex: none; padding-right: 0px;">
							<div class="accordion" id="accordion" style="max-height: 100%; height: 100%;">
								<div class="accordion__card">
									<div class="card-body">
										<table class="accordion__list">
											<tbody id="message_container">
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- end party chat -->
		<? } ?>


	</div>

	<div id="footer">
		<!-- footer -->
		<? require 'inc/html/footer.php' ?>
		<!-- end footer -->
	</div>

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

</body>

</html>
