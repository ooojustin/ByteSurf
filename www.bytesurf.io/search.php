<?php
    
    require 'inc/server.php';
    require 'inc/session.php';
    require_subscription();

    // # of videos shown on each page
	define('VIDEOS_PER_PAGE', 24);

    // all variables and their default values
    $GLOBALS['page'] = 1; // page #

    // set search query, or remove from array
    if (!isset($_GET['q']))
        msg('Uh oh :(', 'Please provide a search query.');

    // update page # if it's set by the user
    if (isset($_GET['page']))
    	$GLOBALS['page'] = intval($_GET['page']);

    // get movies and determine if any videos were found correctly
    $_GET['q'] = '%' . strtolower($_GET['q']) . '%';
    $list = get_matches($_GET['q'], $page);
    if ($page < 1 || count($list) == 0) // PAGE NUMBER IS INVALID or NO MOVIES ARE FOUND
        msg('Oh no :(', 'We couldn\'t find any movies fitting your request.');
        
    function get_matches($query, $page) {
        
        // get all content matching query
        $movies = get_title_matches('movies', $query);
        $shows = get_title_matches('series', $query);
        $animes = get_title_matches('anime', $query);
        
        // array of all matching content
        $list = array_merge($movies, $shows, $animes);
        
        // get data in accordance with page
        $offset = ($page - 1) * VIDEOS_PER_PAGE;
        $list = array_splice($list, $offset, VIDEOS_PER_PAGE);
        
    	return $list;

    }

    function get_title_matches($table, $query) {
        global $db;
        $get_items = $db->prepare('SELECT * FROM `' . $table . '` WHERE LOWER(title) LIKE :query ORDER BY id DESC');
	   	$get_items->bindValue(':query', $query);
    	$get_items->execute();
    	$items = $get_items->fetchAll();
    	foreach ($items as &$item)
            $item['type'] = $table;
        return $items;
    }

  	//echo $get_movies->rowCount() . PHP_EOL;
    function generate_page_url($new_page) {
    	global $current_url, $page;
    	$page_str = 'page=' . $page;
    	$page_str_new = 'page=' . $new_page;
    	if (contains($page_str, $current_url))
    		return str_replace($page_str, $page_str_new, $current_url);
    	else if (count($_GET) == 0)
    		return $current_url . '?' . $page_str_new;
    	else
    		return $current_url . '&' . $page_str_new;
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
	<link rel="stylesheet" href="css/bootstrap-reboot.min.css">
	<link rel="stylesheet" href="css/bootstrap-grid.min.css">
	<link rel="stylesheet" href="css/owl.carousel.min.css">
	<link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
	<link rel="stylesheet" href="css/nouislider.min.css">
	<link rel="stylesheet" href="css/ionicons.min.css">
	<link rel="stylesheet" href="css/plyr.css">
	<link rel="stylesheet" href="css/photoswipe.css">
	<link rel="stylesheet" href="css/default-skin.css">
	<link rel="stylesheet" href="css/main.css">

	<!-- Favicons -->
	<link rel="icon" type="image/png" href="icon/favicon-32x32.png" sizes="32x32">
	<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">

	<meta name="description" content="">
	<meta name="keywords" content="">A
	<meta name="author" content="Peter Pistachio">
	<title>ByteSurf</title>

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
	<script src="js/main.js"></script>

</head>
<body class="body">
	
	<!-- header -->
	<? require 'inc/html/header.php' ?>
	<!-- end header -->


	<!-- page title -->
	<section class="section section--first section--bg" style="margin-bottom: 50px;    ">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section__wrap">
						<!-- section title -->
						<h2 class="section__title">Search Query</h2>
						<!-- end section title -->

						<!-- breadcrumb -->
						<ul class="breadcrumb">
							<li class="breadcrumb__item"><a href="home">Home</a></li>
							<li class="breadcrumb__item breadcrumb__item--active">Movie Catalog</li>
						</ul>
						<!-- end breadcrumb -->
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- end page title -->

	<!-- catalog -->
	<div class="catalog">
		<div class="container">
			<div class="row">
				
				<?
					foreach ($list as $item) {
                        $thumbnail = authenticate_cdn_url($item['thumbnail']);
                        $rating = $item['rating'];
                        switch ($item['type']) {
                            case 'movies': 
                                $script = 'movie.php';
                                $released = $item['year'];
                                break;
                            case 'series': 
                                $script = 'show.php';
                                $released = $item['year'];
                                break;
                            case 'anime': 
                                $script = 'anime.php';
                                $released = $item['release_date'];
                                $rating /= 10;
                                break;
                        }
						$url = sprintf('https://bytesurf.io/%s?t=%s', $script, $item['url']);
				?>   					
    			<div class="col-6 col-sm-4 col-lg-3 col-xl-2">
					<div class="card">
						<div class="card__cover">
							<img src="<?= $thumbnail ?>" alt="">
							<a href="<?= $url ?>" class="card__play">
								<i class="icon ion-ios-play"></i>
							</a>
						</div>
						<div class="card__content">
							<h3 class="card__title"><a href="<?= $url ?>"><?= $item['title'] ?></a></h3>
							<span class="card__category">
                                <a href="<?= $url ?>">Released: <?= $released ?></a>
							</span>
							<span class="card__rate"><i class="icon ion-ios-star"></i><?= $rating ?></span>
						</div>
					</div>
				</div>
				<? } ?>

				<!-- paginator -->
				<script>
					// updates the front/back urls
					function paginate(p1, p2) {
						var paginators = document.getElementsByClassName('paginator__item');
						var prev = document.getElementById("page-prev");
						var next = document.getElementById("page-next");
						prev.href = (paginators[p1]).children[0].href;
						next.href = (paginators[p2]).children[0].href;
                        if (p1 == 1 && p2 == 2) {
                            // first page, fix some things
                            $('.paginator__item').each(function() {
                                var classes = $(this).attr("class").split(' ');
                                var is_active = $(this).hasClass('paginator__item--active');
                                if (!is_active && classes.length == 1)
                                    $(this).remove();
                            });
                        }
					}
				</script>
				<div class="col-12">
					<ul class="paginator">
						<li class="paginator__item paginator__item--prev">
							<a id="page-prev" href="#"><i class="icon ion-ios-arrow-back"></i></a>
						</li>

						<?
							$is_first_page = $page == 1;
							$is_last_page = count(get_matches($_GET['q'], $page + 1)) == 0;
							// it's probably the last page if the number of videos on the next page is 0
							// this can probably be done better but im keeping it this way until the backend code is done
							if ($is_first_page) { ?>
							<li class="paginator__item paginator__item--active"><a href="#"><?= $page ?></a></li>
							<li class="paginator__item"><a href=<?= '"' . generate_page_url($page + 1) . '"' ?>><?= $page + 1 ?></a></li>
							<li class="paginator__item"><a href=<?= '"' . generate_page_url($page + 2) . '"' ?>><?= $page + 2 ?></a></li>
							<script>$(function () { paginate(1, 2) });</script>
							<? } else if ($is_last_page) { ?>
							<li class="paginator__item"><a href=<?= '"' . generate_page_url($page - 2) . '"' ?>><?= $page - 2 ?></a></li>
							<li class="paginator__item"><a href=<?= '"' . generate_page_url($page - 1) . '"' ?>><?= $page - 1 ?></a></li>
							<li class="paginator__item paginator__item--active"><a href="#"><?= $page ?></a></li>
							<script>$(function () { paginate(2, 3) });</script>
							<? } else { ?>
							<li class="paginator__item"><a href=<?= '"' . generate_page_url($page - 1) . '"' ?>><?= $page - 1 ?></a></li>
							<li class="paginator__item paginator__item--active"><a href="#"><?= $page  ?></a></li>
							<li class="paginator__item"><a href=<?= '"' . generate_page_url($page + 1) . '"' ?>><?= $page + 1 ?></a></li>
							<script>$(function () { paginate(1, 3) });</script>
							<? }
						?>

						<!--
						old stuff before making this functional
						<li class="paginator__item"><a href="#">1</a></li>
						<li class="paginator__item paginator__item--active"><a href="#">2</a></li>
						<li class="paginator__item"><a href="#">3</a></li>
						-->

						<li class="paginator__item paginator__item--next">
							<a id="page-next" href="#"><i class="icon ion-ios-arrow-forward"></i></a>
						</li>
					</ul>
				</div>
				<!-- end paginator -->

			</div>
		</div>
	</div>
	<!-- end catalog -->

	
	<!-- footer -->
	<? require 'inc/html/footer.php' ?>
	<!-- end footer -->

</body>
</html>