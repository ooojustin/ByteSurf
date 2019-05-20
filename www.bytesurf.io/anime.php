<?php
	require 'inc/server.php';
	require 'inc/session.php';
	require 'inc/imdb.php';
	require_subscription();

	date_default_timezone_set('UTC');

	// make sure the user has provided an anime
	if (!isset($_GET['t']))
		msg('Uh oh :(', 'Please specify an anime.');

	// get data regarding current anime
	$anime = get_anime_data($_GET['t']);
	if (!$anime)
		msg('Uh oh :(', 'We couldn\'t find that anime.');

	// retrieve raw anime data from cdn server
	$url = authenticate_cdn_url($anime['data'], true);
	$data_raw = file_get_contents($url);
	$data = json_decode($data_raw, true);

	// establish anime title/episodes/image links
	$title = $data['title'];
	$episodes = $data['episodeData'];
	$poster = authenticate_cdn_url($data['poster']);
	$cover = authenticate_cdn_url($data['cover']);

	// if episode isn't set, default it to 1
	default_param('e', 1);

	// get current episode info (note: index = episode # - 1)
	$episode_info = $episodes[$_GET['e'] - 1];

	function generate_mp4_link($res)
	{
		$format = "https://cdn.bytesurf.io/anime/%s/%s/%s.mp4";
		$url = sprintf($format, $_GET['t'], $_GET['e'], $res);
		return $url;
	}

	// default 'watched' button text/value
	$watched = is_watched($_GET['t'], 'anime', -1, $_GET['e']);
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
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
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
		<?php initialize_party_system(); ?>
		<!-- END PARTY SYSTEM -->

		<!-- Favicons -->
		<link rel="icon" type="image/png" href="../icon/favicon-32x32.png" sizes="32x32">
		<link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">

		<meta name="description" content="">
		<meta name="keywords" content="">
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
				<?php require 'inc/html/header.php' ?>
				<!-- end header -->

				<!-- party dialog -->
				<?php $party ? require 'inc/html/party_modal.php' : ''; ?>
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
					<!-- <div class="details__bg" data-bg="img/home/home__bg.jpg"></div> -->
					<!-- end details background -->
					<!-- details content -->
					<div class="container">
						<div class="row">
							<!-- title -->
							<div class="col-12">
								<h1 class="details__title"><?= $title ?></h1>
								<h1 class="details__devices" style="color: #fff"><?= 'Episode ' . $_GET['e']; ?></h1>
								<h1 class="details__devices" style="color: #fff"><?= $episode_info['episode_title']; ?></h1>
							</div>
							<!-- end title -->

							<!-- content -->
							<div class="col-10">
								<div class="card card--details card--series">
									<div class="row">
										<!-- card cover -->
										<div class="col-12 col-sm-4 col-md-4 col-lg-3 col-xl-3">
											<div class="card__cover">
												<img src="<?= $poster ?>" alt="">
											</div>
										</div>
										<!-- end card cover -->
										<!-- card content -->
										<div class="col-12 col-sm-8 col-md-8 col-lg-9 col-xl-9">
											<div class="card__content">
												<div class="card__wrap">
													<span class="card__rate"><i class="icon ion-ios-star"></i><?php echo round($anime['rating'] / 10, 1); ?></span>
													<ul class="card__list">
														<li>HD</li>
													</ul>
												</div>

												<ul class="card__meta">
													<li><span>Genre:</span>
														<?php
														$genres_json = json_decode($anime['genres'], true);
														foreach ($genres_json as $genre) {
															?>
															<a href="#"><?= ucwords($genre) ?></a>
														<?php } ?>
													</li>
													<li><span>Release year: </span><?php echo $anime['release_date']; ?></li>
												</ul>

												<div class="card__description card__description--details">
													<?php echo preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $data['synopsis']); ?>
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
								<video controls crossorigin poster="<?= $cover ?>" id="player">
									<!-- Video files -->
									<?php
									// playsinline
									foreach ($episode_info['qualities'] as $quality) {
										$res = $quality['resolution'];
										$url = generate_mp4_link($res);
										$url = authenticate_cdn_url($url);
										?>
										<source src="<?= $url ?>" type="video/mp4" size="<?= $res ?>"/>
									<?php } ?>

									<!-- Fallback for browsers that don't support the <video> element -->
									<a href="<?= generate_mp4_link($episode_info['qualities'][0]['resolution']) ?>" download>Download</a>

								</video>

								<!-- party btn -->
								<?php
								$party_btn_action = $party ? 'OPEN' : 'CREATE';
								$party_btn_link = $party ? '#' : 'https://bytesurf.io/party.php?action=create';
								$party_a_onclick = $party ? 'return false;' : '';
								?>
								<span style="float: left; padding-top: 10px; padding-bottom: 10px;">
									<a href="<?= $party_btn_link ?>" onclick="<?= $party_a_onclick ?>">
										<button class="filter__btn" id="party-modal-btn" type="button" style="font-size: 10px; height: 35px; width: 170px;"><?= $party_btn_action ?> PARTY</button>
									</a>
								</span>
								<?php if ($party) { ?>
									<script>
										initialize_modal_box('party-modal', 'party-modal-btn');
									</script>
								<?php } ?>
								<!-- end party btn -->

								<!-- watched btn -->
								<span style="float: right; padding-top: 10px; padding-bottom: 10px;">
									<button onclick="toggle_watched(this)" class="filter__btn" name="watchbtn" value="<?= $watched_btn_value ?>" type="button" style="font-size: 10px; height: 35px; width: 170px;"><?= $watched_btn_text ?></button>
								</span>
								<!-- end watched btn -->

							</div>
							<!-- end player -->
						</div>
					</div>
					<!-- end details content -->
				</section>
				<!-- end details -->
				<!-- content -->
				<section class="content">
					<!-- details content -->
					<div class="container">
						<div class="row">
							<!-- accordion -->
							<div class="col-12 col-lg-6" style="max-width: 100%; flex: 100%">
								<div class="accordion" id="accordion">
									<div class="accordion__card">
										<div class="card-body">
											<table class="accordion__list">
												<thead>
													<tr>
														<th style="color:#ff5860">#</th>
														<th style="color:#ff5860">Title</th>
														<th style="color:#ff5860">Air Date</th>
														<th style="color:#ff5860">Watched</th>
													</tr>
												</thead>
												<tbody>
													<?php
													$watched_list = get_progress_tracker_data(true);
													$watched_list_str = stringify_progress_tracker_data($watched_list);
													foreach ($episodes as $episode) {
														$episode_title = empty($episode['episode_title']) ? '-' : $episode['episode_title'];
														$air_date = empty($episode['air_date']) ? '-' : $episode['air_date'];
														$episode_link = "https://bytesurf.io/anime.php?t=" . $_GET['t'] . '&e='	. $episode['episode'];
														$color = ($episode['episode'] == $_GET['e']) ? '#ff5860' : 'rgba(255,255,255,0.7)';
														$item_str = sprintf('%s:%s:%s:%s', 'anime', $_GET['t'], -1, $episode['episode']);
														$episode_watched = in_array($item_str, $watched_list_str);
														?>
														<tr>
															<th><a href="<?= $episode_link ?>" style="color:<?= $color ?>; display: block;"><?= $episode['episode'] ?><a></th>
															<td><a href="<?= $episode_link ?>" style="color:<?= $color ?>; display: block;"><?= $episode_title ?></a></td>
															<td><a href="<?= $episode_link ?>" style="color:<?= $color ?>; display: block;"><?= $air_date ?></a></td>
															<td><a href="<?= $episode_link ?>" style="color:<?= $color ?>; display: block;"><?= $episode_watched ? '✔' : '✘' ?></a></td>
														</tr>
													<?php } ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
							<!-- end accordion -->

				</section>
				<!-- end content -->
			</div>

			<style>
				.header {
					height: 90px;
				}
			</style>

			<script>
				// Hides the header on fullscreen to provide support for mobile players
				function HandleHeader(fullscreened) {
					var header_element = document.getElementById("header");
					header_element.style.display = fullscreened ? "none" : "block"; // remove header
				}

				function FullscreenActuator() {
					// calculate video elements to determine if fullscreen
					var page_height = $(window).height();
					var video_height = $(document.getElementById("player")).height();
					// in fullscreen
					HandleHeader((video_height >= (page_height - 30)));
				}

				window.setInterval(FullscreenActuator, 100);
			</script>

			<?php if ($party) { ?>
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
			<?php } ?>

		</div>

		<div id="footer">
			<!-- footer -->
			<?php require 'inc/html/footer.php' ?>
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
