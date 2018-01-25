  $(document).ready(function() {
    $(".step-info-wrap").click(function(){
    if($(this).parent().hasClass('active')){
      $(this).parent().removeClass('active');
      }
    else {
      $('.col').removeClass('active');
      $(this).parent().addClass('active');
      }
  });
});

//   $(document).ready(function() {
//     $(".steps-action").hide();
//     $(".step-info-wrap").click(function(){
//     if($(this).parent().hasClass('active')){
//       $(this).parent().removeClass('active');
//        $(this).find(".steps-action").slideUp();
//        $(".steps-action").slideUp();
//       }
//     else {
//       $('.col-lg-3').removeClass('active');
//       $(this).parent().addClass('active');
//        $(".steps-action").slideUp();
//        $(this).find(".steps-action").slideDown();
//       }
//   });
// });
/**
 * This module provides a custom slideshow mechanism for use with the header carousel.
 * The transition behavior in this block is too complex to be easily layered upon the
 * default bootstrap carousel.
 */
$(function() {
  // SLIDE_TRANSITION_SPEED should match $slide-transition-speed in _carousel-header.scss.
  var SLIDE_TRANSITION_SPEED = 1000;
  var $headerCarousel = $('#carousel-wrapper-header');
  var $carouselIndicators = $headerCarousel.find('.carousel-indicators');
  var $slides = $headerCarousel.find('.carousel-item');
  var activeTransition = null;

  /**
   * Given an active slide return the next slide, wrapping around the end of the carousel.
   *
   * @param {HTMLElement|jQuery} slide A slide in the carousel.
   * @returns {jQuery} A jQuery selection of the next slide.
   */
  function nextSlide(el) {
    var $el = $(el);
    var $nextSlide = $el.next('.carousel-item');
    // prevAll returns items in reverse DOM order: the first slide is the last element.
    return $nextSlide.length ? $nextSlide : $el.prevAll('.carousel-item').last();
  }

  // Update active slide indicators
  function switchIndicator(index) {
    $carouselIndicators.children().each(function(i, el) {
      $(el).toggleClass('active', i === index);
    });
  }

  $slides.each(function (i, el) {
    var $slide = $(el);

    // Populate carousel indicators list
    $('<li>')
      .attr('data-target', '#carousel-wrapper-header')
      .attr('data-slide-to', i)
      .toggleClass('active', i === 0)
      .appendTo($carouselIndicators);

    // Convert the provided image tag into background image styles.
    var $img = $slide.find('img');
    var $nextImg = nextSlide($slide).find('img');
    $slide
      .css('background-image', 'url(' + $img.attr('src') + ')')
      .css('background-position', $img.data('background-position'));

    // Add an element within the slide to hold the next slide preview.
    var $preview = $('<div>')
      .addClass('carousel-preview-wrap')
      .prependTo($slide);

    $('<div>')
      .addClass('carousel-preview')
      .css('background-image', 'url(' + $nextImg.attr('src') + ')')
      .css('background-position', $nextImg.data('background-position'))
      .appendTo($preview);
  });

  /**
   * Advance to the next slide in the carousel.
   */
  function advanceCarousel() {
    var $active = $slides.filter('.active');
    var $next = nextSlide($active).addClass('next');

    if (activeTransition) {
      // A transition is in progress, so proceed to the next pair of slides
      clearTimeout(activeTransition);
      activeTransition = null;
      $slides.removeClass('fade-out slide-over active next');
      $next.addClass('active');
      nextSlide($next).addClass('next');
      advanceCarousel();
      return;
    }

    // Transition out the active slide
    $active.addClass('slide-over');

    switchIndicator($slides.index($next));

    // When transition is done, swap out the slides
    activeTransition = setTimeout(function beginTransition() {
      $active.addClass('fade-out');
      activeTransition = setTimeout(function completeTransition() {
        $active.removeClass('active');
        $slides.removeClass('slide-over fade-out');
        $next.removeClass('next').addClass('active');
        // Ensure the new upcoming slide has .next
        nextSlide($next).addClass('next');
        activeTransition = null;
      }, SLIDE_TRANSITION_SPEED / 2);
    }, SLIDE_TRANSITION_SPEED);
  }

  /**
   * Switch to a specific slide.
   *
   * @param {Number} slideIndex The index of a slide in the carousel.
   */
  function activate(slideIndex) {
    var $slide = $slides.eq(slideIndex);

    if ($slide.hasClass('active') && !$slide.hasClass('slide-over')) {
      // If the requested slide is active and not transitioning, do nothing.
      return;
    }

    if ($slide.hasClass('next')) {
      // If the slide being requested is next, transition normally.
      advanceCarousel();
      return;
    }

    if (activeTransition) {
      clearTimeout(activeTransition);
    }

    switchIndicator(slideIndex);

    $slides.removeClass('active next slide-over fade-out');
    $slide.addClass('active');
    nextSlide($slide).addClass('next');
  }

  // Bind mouse interaction events
  $headerCarousel
    .on('click', '.carousel-control-next', function(evt) {
      evt.preventDefault();
      advanceCarousel();
    })
    .on('click', '.carousel-indicators li', function (evt) {
      evt.preventDefault();
      activate($(evt.target).data('slide-to'));
    });
});

function createCookie(name,value,days) {
  document.cookie = encodeURI(name) + '=' + encodeURI(value) + ';domain=.' + document.domain + ';path=/;';
}

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}

$(function() {
  cookie = readCookie('greenpeace');
  if (cookie == null) {
    $(".cookie-block").show();
  }
});

$("#hidecookie").click(function () {
  $(".cookie-block").slideUp("slow");
  createCookie('greenpeace', 'policy-accepted');
});

$('.country-select-dropdown').click(function(){
  $(this).parent().toggleClass('active-li');
  $('.country-select-box').toggle();
});

$('.country-select-box .country-list li').click(function(){
  $(this).parents('.country-select-box').find('li').removeClass('active');
  $(this).addClass('active');
});

$('.country-selectbox').click(function(){
  $(this).toggleClass('active');	
  $(this).parent().find('.option-contry').toggleClass('active');
});

  // $(window).scroll(function() {
  //   if ($(this).scrollTop() > 130){  
  //       $('.fixed-element, .md-navigation').addClass("sticky");
  //   }
  //   else{
  //       $('.fixed-element, .md-navigation').removeClass("sticky");
  //   }
  // });
// Footer JS goes in this
// Header JS goes in this.

$(document).on('click', [
  '.navbar-dropdown-toggle',
  '.country-dropdown-toggle',
  '.navbar-search-toggle',
].join(), function toggleNavDropdown(evt) {
  evt.preventDefault();
  evt.stopPropagation();

  var $button = $(this);
  var target = $button.data('target');
  if (!target) {
    throw new Error('Missing `data-target` attribute: specify the container to be toggled');
  }
  var toggleClass = $button.data('toggle');
  if (!toggleClass) {
    throw new Error('Missing `data-toggle` attribute: specify the class to toggle');
  }

  // Toggle visibility of the target specified via data-target.
  $(target).toggleClass(toggleClass);
  // Toggle aria-expanded attribute.
  $button.attr('aria-expanded', function(i, attr) {
    return attr === 'false' ? 'true' : 'false';
  });
});

// Close all menus when clicking somewhere else
$(document).on('click', function closeInactiveMenus(evt) {
  var clickedElement = evt.target;
  $('button[aria-expanded="true"]').each(function(i, button) {
    var $button = $(button);
    var buttonTarget = $($button.data('target')).get( 0 );
    if (buttonTarget && ! $.contains(buttonTarget, clickedElement)) {
      // Spoof a click on the open menu's toggle to close that menu.
      $button.trigger('click');
    }
  });
});
// Close all menus on escape pressed
$(document).bind('keyup', function(event){
  if (event.which === 27) {
    $(document).trigger('click');
  }
});
/**
 * Close the menu if the user clicks the dedicated dropdown close button.
 */
$(document).on('click', '.close-navbar-dropdown', function (evt) {
  evt.preventDefault();
  // Proxy to the main navbar close button
  $('.navbar-dropdown-toggle').trigger('click');
});

// Hide Header on on scroll down
if($( window ).width() <= 768) {
  var didScroll;
  var lastScrollTop = 0;
  var delta = 5;
  var navbarHeight = $('.top-navigation').outerHeight();
  $(window).scroll(function(event){
      didScroll = true;
  });
  setInterval(function() {
      if (didScroll) {
          hasScrolled();
          didScroll = false;
      }
  }, 250);
  function hasScrolled() {
      var st = $(this).scrollTop();
      if(Math.abs(lastScrollTop - st) <= delta)
          return;
      if (st > lastScrollTop && st > navbarHeight){
          $('.top-navigation').removeClass('nav-down').addClass('nav-up');
      } else {
          if(st + $(window).height() < $(document).height()) {
              $('.top-navigation').removeClass('nav-up').addClass('nav-down');
          }
      }
      lastScrollTop = st;
  }
  var $slider = $('.mobile-menus');
  $(document).click(function() {
    if($('.menu').hasClass('active')){
    //Hide the menus if visible
    $slider.animate({
      left: parseInt($slider.css('left'),10) == 0 ?
       -320 : 0
    });
    $('.menu').removeClass('active');
    }
    if($('.search-box').hasClass('active')){
    //Hide the search if visible
    $searchBox.slideToggle().toggleClass('active');;
    }
  });

  $('.menu').click(function() {
    event.stopPropagation();
    $(this).toggleClass('active');
    $slider.animate({
      left: parseInt($slider.css('left'),10) == -320 ?
       0 : -320
    });
  });

  var $searchBox = $('#search .search-box');
  var $searchTrigger = $('#search-trigger');

  $searchTrigger.on('click', function(e) {
    event.stopPropagation();
      $searchBox.slideToggle().toggleClass('active');
  });
};

$(function() {
  // Force the Cover card to follow scroll
  var $sidebar = $("#action-card");
  var $window = $(window);
  var offset = $sidebar.offset();
  var topPadding = 100;

  if ($sidebar.length > 0) {
    var absPosition = $('.post-details > p:last-child').offset().top - $sidebar.outerHeight() - topPadding;

    $window.scroll(function () {
      if ($window.scrollTop() > offset.top &&
          $window.scrollTop() < absPosition &&
          $sidebar.css('float') != 'none') {
        $sidebar.stop().animate({
          marginTop: $window.scrollTop() - offset.top + topPadding
        });
      }
      if ($window.scrollTop() < offset.top) {
        $sidebar.stop().animate({
          marginTop: 0
        });
      }
    });
  }
});

$(function() {
	$('.publications-slider').slick({
		infinite:       false,
		mobileFirst:    true,
		slidesToShow:   2.2,
		slidesToScroll: 1,
		arrows:         false,
		dots:           false,
		responsive: [
			{
				breakpoint: 992,
				settings: { slidesToShow: 4 }
			},
			{
				breakpoint: 768,
				settings: { slidesToShow: 3 }
			},
			{
				breakpoint: 576,
				settings: { slidesToShow: 2 }
			}
		]
	});
});



