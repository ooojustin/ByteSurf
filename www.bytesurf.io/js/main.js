// sends an async get request to a provided url
// second parameter is a callback function with a response param
function get_request(url, callback) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() { 
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
            callback(xmlHttp.responseText);
    }
    xmlHttp.open("GET", url, true); // true for asynchronous 
    xmlHttp.send(null);
}

function send_update(action, params, callback) {
    let query = jQuery.param(params);
    let url = 'https://bytesurf.io/inc/updater.php?action=';
    url += action + '&' + query;
    get_request(url, callback);
}

// checks if a key exists in an array
function array_key_exists(key, arr) {
    return (key in arr);
}

// checks if a given media type is valid
function is_media_type_valid(type) {
    let types = ['movie', 'show', 'anime'];
    return types.includes(type);
}

// returns the type of media (file name without .php extension)
function get_media_type() {
    let path = window.location.pathname;
    let file_name = path.substring(path.lastIndexOf('/') + 1);
    let type = file_name.replace('.php', '');
    return type;
}

// populates an array with important variables from the current request
// includes season, episode, and title
function get_important_params() {
    
    // find season, episode, and title.
    // put them into a key/value array that we can build a query from.
    let find = ['s', 'e', 't'];
    let params = { };
         
    // initialize URL and URLSearchParams objects so we can get params from URL
    let url_obj = new URL(window.location.href);
    let searchParams = new URLSearchParams(url_obj.search);
    
    // put items from url into our own array
    find.forEach(function(f) {
        if (searchParams.has(f)) {
            params[f] = searchParams.get(f);
        }
    });
    
    // store type & update 's' & 'e' if necessary
    params['type'] = get_media_type();
    populate_seasons_and_episodes(params, params['type']);
    
    return params;
    
}

function populate_seasons_and_episodes(params, type) {
    switch (type) {
        case 'show':
            if (!array_key_exists('s', params))
                params['s'] = 1;
            if (!array_key_exists('e', params))
                params['e'] = 1;
            break;
        case 'anime':
            params['s'] = -1;
            if (!array_key_exists('e', params))
                params['e'] = 1;
            break;
        case 'movie':
            params['s'] = -1;
            params['e'] = -1;
            break;
    }
}

// onclick="toggle_watched(this)" <--- add to a button :D
function toggle_watched(btn) {  
    send_update(btn.value, get_important_params(), function(r) {
        let data = r.split(':');
        btn.value = data[0];
        btn.innerHTML = data[1];
    });
}

// toggles whether or not the current video is queued
function toggle_queued() {
    send_update('toggle_queued', get_important_params(), function(r) {
        console.log(r);
    });
}

$(document).ready(function () {
    
	"use strict"; // start of use strict
    
	/*==============================
	Menu
	==============================*/
    
	$('.header__btn').on('click', function () {
		$(this).toggleClass('header__btn--active');
		$('.header__nav').toggleClass('header__nav--active');
		$('.body').toggleClass('body--active');
		if ($('.header__search-btn').hasClass('active')) {
			$('.header__search-btn').toggleClass('active');
			$('.header__search').toggleClass('header__search--active');
		}
	});
    
	/*==============================
	Search
	==============================*/
    
	$('.header__search-btn').on('click', function () {
		$(this).toggleClass('active');
		$('.header__search').toggleClass('header__search--active');
		if ($('.header__btn').hasClass('header__btn--active')) {
			$('.header__btn').toggleClass('header__btn--active');
			$('.header__nav').toggleClass('header__nav--active');
			$('.body').toggleClass('body--active');
		}
	});
    
	/*==============================
	Home
	==============================*/
    
	$('.home__bg').owlCarousel({
		animateOut: 'fadeOut',
		animateIn: 'fadeIn',
		mouseDrag: false,
		touchDrag: false,
		items: 1,
		dots: false,
		loop: true,
		autoplay: false,
		smartSpeed: 600,
		margin: 0,
	});
    
	$('.home__bg .item').each(function () {
		if ($(this).attr("data-bg")) {
			$(this).css({
				'background': 'url(' + $(this).data('bg') + ')',
				'background-position': 'center center',
				'background-repeat': 'no-repeat',
				'background-size': 'cover'
			});
		}
	});
    
	$('.home__carousel').owlCarousel({
		mouseDrag: false,
		touchDrag: false,
		dots: false,
		loop: true,
		autoplay: false,
		smartSpeed: 600,
		margin: 30,
		responsive: {
			0: {
				items: 2,
			},
			576: {
				items: 2,
			},
			768: {
				items: 3,
			},
			992: {
				items: 4,
			},
			1200: {
				items: 4,
				margin: 50
			},
		}
	});
    
	$('.home__nav--next').on('click', function () {
		$('.home__carousel, .home__bg').trigger('next.owl.carousel');
	});
    
	$('.home__nav--prev').on('click', function () {
		$('.home__carousel, .home__bg').trigger('prev.owl.carousel');
	});
    
	$(window).on('resize', function () {
		var itemHeight = $('.home__bg').height();
		$('.home__bg .item').css("height", itemHeight + "px");
	});
    
	$(window).trigger('resize');
    
	/*==============================
	Tabs
	==============================*/
    
	$('.content__mobile-tabs-menu li').each(function () {
		$(this).attr('data-value', $(this).text().toLowerCase());
	});
    
	$('.content__mobile-tabs-menu li').on('click', function () {
		var text = $(this).text();
		var item = $(this);
		var id = item.closest('.content__mobile-tabs').attr('id');
		$('#' + id).find('.content__mobile-tabs-btn input').val(text);
        
	});
	/*==============================
	Section bg
	==============================*/
    
	$('.section--bg, .details__bg').each(function () {
		if ($(this).attr("data-bg")) {
			$(this).css({
				'background': 'url(' + $(this).data('bg') + ')',
				'background-position': 'center center',
				'background-repeat': 'no-repeat',
				'background-size': 'cover'
			});
		}
	});
    
	/*==============================
	Filter
	==============================*/
    
	$('.filter__item-menu li').each(function () {
		$(this).attr('data-value', $(this).text().toLowerCase());
	});
	$('.filter__item-menu li').on('click', function () {
		var text = $(this).text();
		var item = $(this);
		var id = item.closest('.filter__item').attr('id');
		$('#' + id).find('.filter__item-btn input').val(text);
	});
    
	/*==============================
	Scroll bar
	==============================*/
    
	$('.scrollbar-dropdown').mCustomScrollbar({
		axis: "y",
		scrollbarPosition: "outside",
		theme: "custom-bar"
	});
	$('.accordion').mCustomScrollbar({
		axis: "y",
		scrollbarPosition: "outside",
		theme: "custom-bar2"
	});
    
	/*==============================
	Morelines
	==============================*/
    
	$('.card__description--details').moreLines({
		linecount: 6,
		baseclass: 'b-description',
		basejsclass: 'js-description',
		classspecific: '_readmore',
		buttontxtmore: "",
		buttontxtless: "",
		animationspeed: 400
	});
    
	/*==============================
	Gallery
	==============================*/
    
	var initPhotoSwipeFromDOM = function (gallerySelector) {
		// parse slide data (url, title, size ...) from DOM elements 
		// (children of gallerySelector)
		var parseThumbnailElements = function (el) {
			var thumbElements = el.childNodes,
				numNodes = thumbElements.length,
				items = [],
				figureEl,
				linkEl,
				size,
				item;
			for (var i = 0; i < numNodes; i++) {
				figureEl = thumbElements[i]; // <figure> element
				// include only element nodes 
				if (figureEl.nodeType !== 1) {
					continue;
				}
				linkEl = figureEl.children[0]; // <a> element
				size = linkEl.getAttribute('data-size').split('x');
				// create slide object
				item = {
					src: linkEl.getAttribute('href'),
					w: parseInt(size[0], 10),
					h: parseInt(size[1], 10)
				};
				if (figureEl.children.length > 1) {
					// <figcaption> content
					item.title = figureEl.children[1].innerHTML;
				}
				if (linkEl.children.length > 0) {
					// <img> thumbnail element, retrieving thumbnail url
					item.msrc = linkEl.children[0].getAttribute('src');
				}
				item.el = figureEl; // save link to element for getThumbBoundsFn
				items.push(item);
			}
			return items;
		};
        
		// find nearest parent element
		var closest = function closest(el, fn) {
			return el && (fn(el) ? el : closest(el.parentNode, fn));
		};
        
		// triggers when user clicks on thumbnail
		var onThumbnailsClick = function (e) {
			e = e || window.event;
			e.preventDefault ? e.preventDefault() : e.returnValue = false;
			var eTarget = e.target || e.srcElement;
			// find root element of slide
			var clickedListItem = closest(eTarget, function (el) {
				return (el.tagName && el.tagName.toUpperCase() === 'FIGURE');
			});
			if (!clickedListItem) {
				return;
			}
			// find index of clicked item by looping through all child nodes
			// alternatively, you may define index via data- attribute
			var clickedGallery = clickedListItem.parentNode,
				childNodes = clickedListItem.parentNode.childNodes,
				numChildNodes = childNodes.length,
				nodeIndex = 0,
				index;
			for (var i = 0; i < numChildNodes; i++) {
				if (childNodes[i].nodeType !== 1) {
					continue;
				}
				if (childNodes[i] === clickedListItem) {
					index = nodeIndex;
					break;
				}
				nodeIndex++;
			}
			if (index >= 0) {
				// open PhotoSwipe if valid index found
				openPhotoSwipe(index, clickedGallery);
			}
			return false;
		};
        
		// parse picture index and gallery index from URL (#&pid=1&gid=2)
		var photoswipeParseHash = function () {
			var hash = window.location.hash.substring(1),
				params = {};
			if (hash.length < 5) {
				return params;
			}
			var vars = hash.split('&');
			for (var i = 0; i < vars.length; i++) {
				if (!vars[i]) {
					continue;
				}
				var pair = vars[i].split('=');
				if (pair.length < 2) {
					continue;
				}
				params[pair[0]] = pair[1];
			}
			if (params.gid) {
				params.gid = parseInt(params.gid, 10);
			}
			return params;
		};
        
		var openPhotoSwipe = function (index, galleryElement, disableAnimation, fromURL) {
			var pswpElement = document.querySelectorAll('.pswp')[0],
				gallery,
				options,
				items;
			items = parseThumbnailElements(galleryElement);
			// define options (if needed)
			options = {
				// define gallery index (for URL)
				galleryUID: galleryElement.getAttribute('data-pswp-uid'),
				getThumbBoundsFn: function (index) {
					// See Options -> getThumbBoundsFn section of documentation for more info
					var thumbnail = items[index].el.getElementsByTagName('img')[0], // find thumbnail
						pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
						rect = thumbnail.getBoundingClientRect();
					return {
						x: rect.left,
						y: rect.top + pageYScroll,
						w: rect.width
					};
				}
			};
			// PhotoSwipe opened from URL
			if (fromURL) {
				if (options.galleryPIDs) {
					// parse real index when custom PIDs are used 
					// http://photoswipe.com/documentation/faq.html#custom-pid-in-url
					for (var j = 0; j < items.length; j++) {
						if (items[j].pid == index) {
							options.index = j;
							break;
						}
					}
				} else {
					// in URL indexes start from 1
					options.index = parseInt(index, 10) - 1;
				}
			} else {
				options.index = parseInt(index, 10);
			}
			// exit if index not found
			if (isNaN(options.index)) {
				return;
			}
			if (disableAnimation) {
				options.showAnimationDuration = 0;
			}
			// Pass data to PhotoSwipe and initialize it
			gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
			gallery.init();
		};
		// loop through all gallery elements and bind events
		var galleryElements = document.querySelectorAll(gallerySelector);
		for (var i = 0, l = galleryElements.length; i < l; i++) {
			galleryElements[i].setAttribute('data-pswp-uid', i + 1);
			galleryElements[i].onclick = onThumbnailsClick;
		}
		// Parse URL and open gallery if it contains #&pid=3&gid=1
		var hashData = photoswipeParseHash();
		if (hashData.pid && hashData.gid) {
			openPhotoSwipe(hashData.pid, galleryElements[hashData.gid - 1], true, true);
		}
	};
	// execute above function
	initPhotoSwipeFromDOM('.gallery');
    
	/*==============================
	Player
	==============================*/
    
	function initializePlayer() {
		if ($('#player').length) {
			const player = new Plyr('#player');
		} else {
			return false;
		}
		return false;
	}
	$(window).on('load', initializePlayer());
    
	/*==============================
	Range sliders
	==============================*/
    
	/*1*/
	function initializeFirstSlider() {
		if ($('#filter__years').length) {
			var firstSlider = document.getElementById('filter__years');
			noUiSlider.create(firstSlider, {
				range: {
					'min': 1900,
					'max': 2019
				},
				step: 1,
				connect: true,
				start: [2000, 2019],
				format: wNumb({
					decimals: 0,
				})
			});
			var firstValues = [
				document.getElementById('filter__years-start'),
				document.getElementById('filter__years-end')
			];
			firstSlider.noUiSlider.on('update', function (values, handle) {
				firstValues[handle].innerHTML = values[handle];
			});
		} else {
			return false;
		}
		return false;
	}
	$(window).on('load', initializeFirstSlider());
    
	/*2*/
	function initializeSecondSlider() {
		if ($('#filter__imbd').length) {
			var secondSlider = document.getElementById('filter__imbd');
			noUiSlider.create(secondSlider, {
				range: {
					'min': 0,
					'max': 10
				},
				step: 0.1,
				connect: true,
				start: [0.1, 10.0],
				format: wNumb({
					decimals: 1,
				})
			});
			var secondValues = [
				document.getElementById('filter__imbd-start'),
				document.getElementById('filter__imbd-end')
			];
			secondSlider.noUiSlider.on('update', function (values, handle) {
				secondValues[handle].innerHTML = values[handle];
			});
			$('.filter__item-menu--range').on('click.bs.dropdown', function (e) {
				e.stopPropagation();
				e.preventDefault();
			});
		} else {
			return false;
		}
		return false;
	}
	$(window).on('load', initializeSecondSlider());
    
	/*3*/
	function initializeThirdSlider() {
		if ($('#slider__rating').length) {
			var thirdSlider = document.getElementById('slider__rating');
			noUiSlider.create(thirdSlider, {
				range: {
					'min': 0,
					'max': 10
				},
				connect: [true, false],
				step: 0.1,
				start: 8.6,
				format: wNumb({
					decimals: 1,
				})
			});
			var thirdValue = document.getElementById('form__slider-value');
			thirdSlider.noUiSlider.on('update', function (values, handle) {
				thirdValue.innerHTML = values[handle];
			});
		} else {
			return false;
		}
		return false;
	}
	$(window).on('load', initializeThirdSlider());
    
	/*====================================details3.html============================================*/
	$('.owl-carousel').owlCarousel({
		mouseDrag: true,
		touchDrag: true,
		dots: true,
		loop: true,
		autoplay: false,
		smartSpeed: 600,
		margin: 30,
		responsive: {
			0: {
				items: 2,
			},
			576: {
				items: 2,
			},
			768: {
				items: 3,
			},
			992: {
				items: 4,
			},
			1200: {
				items: 4,
				margin: 50
			},
			1440: {
				items: 5,
				//margin: 50
			},
		}
	});
    
    /* ======== FILTERING ======= */
	document.getElementById("catalog-submit").addEventListener("click", function () {
		document.getElementById("imdb_min").value = document.getElementById("filter__imbd-start").innerHTML;
		document.getElementById("imdb_max").value = document.getElementById("filter__imbd-end").innerHTML;
		document.getElementById("year_min").value = document.getElementById("filter__years-start").innerHTML;
		document.getElementById("year_max").value = document.getElementById("filter__years-end").innerHTML;
	})
    
});

function initialize_modal_box(modal_id, button_id) {
    
    // Get the modal
    var modal_box = document.getElementById(modal_id);

    // Get the button that opens the modal
    var modal_btn = document.getElementById(button_id);

    // Get the <span> element that closes the modal
    var close_span = document.getElementsByClassName('modal-close')[0];

    // When the user clicks the button, open the modal 
    modal_btn.onclick = function() {
        modal_box.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    close_span.onclick = function() {
        modal_box.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal_box) {
            modal_box.style.display = "none";
        }
    }
    
}