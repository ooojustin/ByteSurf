<?php

    require 'inc/server.php';
    require 'inc/session.php';
    require 'inc/imdb.php';
    require_subscription();

    global $user;

    if (!isset($_GET['t']))
        msg('Error', 'Movie not specified.');

    $data = get_movie_data($_GET['t']);
    if (!$data)
        msg('Error', 'Movie not found.');

    update_imdb_information($_GET['t']);

    $title = $data['title'];
    $description = $data['description'];
    $thumbnail = authenticate_cdn_url($data['thumbnail']);
    $preview = authenticate_cdn_url($data['preview']);
    $year = $data['year'];
    $certification = $data['certification'];
    $duration = intval($data['duration'] / 60);
    $qualities = authenticated_movie_links($data);
    $rating = $data['rating'];
    $subs = get_subtitles($data);

    // default 'watched' button text/value
    $watched = is_watched($_GET['t'], 'movie');
    $watched_btn_text = $watched ? 'REMOVE FROM WATCHED' : 'ADD TO WATCHED';
    $watched_btn_value = $watched ? 'remove_from_watched' : 'add_to_watched';

    // get user party
    $party = get_active_party();

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

</head>

<body class="body">

    <div style="width: 100%; overflow: hidden;">

        <div id="left_section">

            <style>
                .header {
                    height: 90px;
                }

                .accordion {
                    max-height: 100%;
                    height: 100%;
                }
            </style>

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
                <!--<div class="details__bg" data-bg="img/home/home__bg.jpg"></div>-->
                <!-- end details background -->
                <div class="container">

                    <div class="row">

                        <!-- player -->
                        <div class="col-12">
                            <video controls crossorigin playsinline poster="<?= $preview ?>" id="player">

                                <!-- Video files -->
                                <? foreach ($qualities as $quality) { ?>
                                    <source src="<?= $quality['link'] ?>" type="video/mp4" size="<?= $quality['resolution'] ?>" />
                                <? } ?>

                                <!-- Caption files -->
                                <?
                                foreach ($subs as $sub) {
                                    $sub_end = isset($sub['default']) ? ' default' : '';
                                    ?>
                                    <track kind="captions" label="<?= $sub['label'] ?>" srclang="<?= $sub['language'] ?>" src="<?= authenticate_cdn_url($sub['url']) ?>" <?= $sub_end ?>>
                                <? } ?>

                                <!-- Fallback for browsers that don't support the <video> element -->
                                <a href=<?= '"' . $qualities[0]['link'] . '"' ?> download>Download</a>
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
                            <span style="float: right; padding-top: 10px; padding-bottom: 10px;">
                                <button onclick="toggle_watched(this)" class="filter__btn" name="watchbtn" value="<?= $watched_btn_value ?>" type="button" style="font-size: 10px; height: 35px; width: 170px;"><?= $watched_btn_text ?></button>
                            </span>
                            <!-- end watched btn -->

                        </div>
                        <!-- end player -->
                    </div>

                </div>




            </section>
            <!-- end details -->

            <!-- content -->
            <section class="content">
                <!-- details content -->
                <div class="container">

                    <div class="row">
                        <div class="col-12 col-lg-6 ">
                            <div class="movie-disc">
                                <div class="movie-cover-img">
                                    <div class="card__cover cover-avatar-img">
                                        <img src=<?= $thumbnail ?> alt="">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-12 col-lg-6 ">
                            <div class="card__content">
                                <div class="content-heading">
                                    <h2 class="details__title"><?= $title ?></h2>
                                </div>
                                <div class="card__wrap">
                                    <span class="card__rate"><i class="icon ion-ios-star"></i><?= $rating ?></span>

                                    <ul class="card__list">
                                        <li>HD</li>
                                        <li><?= $certification ?></li>
                                        <li><?= $year ?></li>
                                        <li><?= $duration ?>min</li>
                                    </ul>
                                </div>

                                <ul class="card__meta">
                                    <li><span>Genre:</span> <a href="#">Action</a>
                                        <a href="#">Triler</a></li>
                                    <li><span>Release year:</span> 2017</li>
                                    <li><span>Running time:</span> 120 min</li>
                                    <li><span>Country:</span> <a href="#">USA</a></li>
                                </ul>

                                <span class="card__category">
                                    <a href="https://bytesurf.io/contact/?q=problem&t=<?= $title ?>" target="blank">Report a Problem</a>
                                </span>

                                <div class="card__description card__description--details">
                                    <p>
                                        <?= $description ?>
                                    </p>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- end details content -->
            </section>
            <!-- end content -->

            <!-- similar movies -->
            <section>
                <div class="container-fluid">
                    <div class="row>">
                        <div class="col-12">
                            <div class="owl-carousel">

                                <?
                                $similar = get_similar_movies($_GET['t']);
                                foreach ($similar as $movie) {
                                    $movie = get_movie_data($movie);
                                    $thumbnail = authenticate_cdn_url($movie['thumbnail']);
                                    $href = 'https://bytesurf.io/movie.php?t=' . $movie['url'];
                                    ?>
                                    <div class="card">
                                        <div class="card__cover">
                                            <img src=<?= '"' . $thumbnail . '"'; ?> alt="">
                                            <a href=<?= '"' . $href . '"'; ?> class="card__play">
                                                <i class="icon ion-ios-play"></i>
                                            </a>
                                        </div>
                                        <div class="card__content">
                                            <h3 class="card__title"><a href=<?= '"' . $href . '"'; ?>><?= $movie['title'] ?></a></h3>
                                            <span class="card__category">
                                                <a href=<?= '"https://bytesurf.io.com/catalog/?year_min=' . $movie['year'] . '&year_max=' . $movie['year'] . '"' ?>>Released: <?= $movie['year']    ?></a>
                                            </span>
                                            <span class="card__rate"><i class="icon ion-ios-star"></i><?= $movie['rating'] ?></span>
                                        </div>
                                    </div>
                                <? } ?>

                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- end similar movies -->
        </div>


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
                    window.setTimeout("set_chat_elements();", 1);
                }

                $(document).ready(function() {

                    document.getElementById("chatbtn").addEventListener("click", toggle_chat);
                    document.getElementById("chatbtn").addEventListener("click", set_chat_elements);
                    window.addEventListener("scroll", set_chat_elements, false);
                    window.addEventListener("resize", set_chat_elements, false);
                    window.addEventListener("load", set_chat_elements, false);
                    //window.onload = set_timeoutfunction;

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
                            <div class="accordion" id="accordion">
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
<?php

function get_subtitles($data)
{

    if (empty($data['subtitles']))
        return array();
    else
        $subs = json_decode($data['subtitles'], true);

    $subs[0]['default'] = 'true';
    return $subs;
}

?>