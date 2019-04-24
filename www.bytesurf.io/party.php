<?php

    require 'inc/server.php';
    require 'inc/session.php';

    global $db, $user;
    require_subscription();

    // make sure action is set and valid
    if (!isset($_GET['action']))
        msg('Failed', 'Something went wrong :(');

    switch ($_GET['action']) {
            
        case 'create':
            
            // try to create a party using referrer link
            $ref = $_SERVER['HTTP_REFERER'] ?: null;
            if (!is_null($ref)) {
                $ref_type = get_type($ref);
                if (validate_type($ref_type)) {
                    $ref_query = parse_url($ref, PHP_URL_QUERY);
                    parse_str($ref_query, $ref_params);
                    if (isset($ref_params['t'])) {
                        chrome_php::log('REF LINK PARAMS: ' . json_encode($ref_params, JSON_PRETTY_PRINT));
                        default_param('s', -1, $ref_params);
                        default_param('e', -1, $ref_params);
                        if ($ref_type == 'anime' || $ref_type == 'show')
                            default_param('e', 1, $ref_params);
                        if ($ref_type == 'show')
                            default_param('s', 1, $ref_params);
                        $party_id = create_party($ref_params['t'], $ref_type, $ref_params['s'], $ref_params['e']);
                    }
                }
            }
            
            // create party normally
            if (!isset($party_id))
                $party_id = create_party();
            
            $join_url = 'https://bytesurf.io/party.php?action=join&p=' . $party_id;
            $continue_url = get_active_party_url() ?: 'https://bytesurf.io/home';
            
            break;
            
        case 'leave';
            
            unset($_SESSION['party']);
            msg('Left Party', 'You can go back to browsing ByteSurf alone :(', 'GO HOME', 'https://bytesurf.io/home');
            
            break;
            
        case 'join':
            
            // make sure we have party set in url
            if (!isset($_GET['p']))
                msg('Failed', 'Party ID not specified :(', 'GO HOME', 'https://bytesurf.io/home');
            
            // make sure the party is valid
            $party = get_party($_GET['p']);
            if (!$party)
                msg('Failed', 'Specified party ID was invalid :(', 'GO HOME', 'https://bytesurf.io/home');
            
            // make sure the host is still running the party
            $update_delta = time_ms() - $party['timestamp'];
            if ($update_delta > 60000)
                msg('Failed', 'That party is inactive. Create your own, or join another one.', 'GO HOME', 'https://bytesurf.io/home');
                
            // join the party
            $_SESSION['party'] = $_GET['p'];
            
            // redirect to current link
            if ($url = get_active_party_url()) {
                header('location: ' . $url);
                die();
            }
            
            // output a basic message if we couldnt redirect to a party url
            msg('Party Joined', 'You have joined a party successfully. When the host begins to watch a video, you can be redirected there automatically by clicking any movie, show, or anime.', 'CONTINUE', 'https://bytesurf.io/home');
            
            break;
            
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
    
    <!-- C
    
	<!-- Favicons -->
	<link rel="icon" type="image/png" href="../icon/favicon-32x32.png" sizes="32x32">
	<link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">

	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="Peter Pistachio">
	<title>ByteSurf - Party System [beta]</title>

</head>
<body class="body">

	<div class="sign section--bg" data-bg="../img/section/section.jpg">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="sign__content">
				        
                        <!-- CREATE PARTY -->
                        <? if ($_GET['action'] == 'create') { ?>
						<form action="#" method="GET" class="sign__form" style="width: 70%">
							<a href="#" style="margin-bottom: 20px;" class="sign__logo">
								<img src="../img/logo_party.png" alt="">
							</a>
                            <script>
                                function copy_link() {
                                    let txt = document.getElementById("join_url");
                                    txt.select();
                                    document.execCommand("copy");
                                    alert('Party URL has been copied to clipboard.');
                                }
                                function continue_to_party() {
                                    window.location.href = "<?= $continue_url ?>";
                                }
                            </script>
                            <center style="margin-bottom: 15px;"><span class="sign__text">Party created. Give this link to others to allow them to join your party.</span></center>
							<div class="sign__group" style="width: 95%">
								<input type="text" style="width: 100%; text-align: center;" class="sign__input" id="join_url" name="join_url" value="<?= $join_url ?>" readonly>
							</div>					
							<button style="margin-top: 0px" onclick="copy_link()" class="sign__btn" type="button">COPY LINK</button>
                            <button style="margin-top: 15px" onclick="continue_to_party()" class="sign__btn" type="button">CONTINUE</button>
						</form>
						<? } ?>
                        <!-- END CREATE PARTY -->
                        
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>