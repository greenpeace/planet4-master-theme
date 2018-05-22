/* global Hammer */

$(document).ready(function() {
  'use strict'

  // Add event listeners when window is resized, change the carousel's images background attribute to the current loaded ones.
  $('#carousel-wrapper-header').find('img').on('load', function () {
    var current_img_src = $(this).get(0).currentSrc
      , current_bg_img = $(this).parent().css('background-image').replace(/.*\s?url\(['"]?/, '').replace(/['"]?\).*/, '')
    if (current_img_src !== current_bg_img) {
      $(this).parent().css('background-image', 'url(' + $(this).get(0).currentSrc + ')')
    }
  })

  /**
  * This module provides a custom slideshow mechanism for use with the header carousel.
  * The transition behavior in this block is too complex to be easily layered upon the
  * default bootstrap carousel.
  */

  // SLIDE_TRANSITION_SPEED should match $slide-transition-speed in _carousel-header.scss.
  var SLIDE_TRANSITION_SPEED = 1000
    , $headerCarousel = $('#carousel-wrapper-header')
    , $carouselIndicators = $headerCarousel.find('.carousel-indicators')
    , $slides = $headerCarousel.find('.carousel-item')
    , activeTransition = null

  /**
  * Given an active slide return the next slide, wrapping around the end of the carousel.
  *
  * @param {HTMLElement|jQuery} slide A slide in the carousel.
  * @returns {jQuery} A jQuery selection of the next slide.
  */
  function nextSlide(el) {
    var $el = $(el)
      , $nextSlide = $el.next('.carousel-item')
    // prevAll returns items in reverse DOM order: the first slide is the last element.
    return $nextSlide.length ? $nextSlide : $el.prevAll('.carousel-item').last()
  }

  function previousSlide(el) {
    var $el = $(el)
      , $previousSlide = $el.prev('.carousel-item')
    return $previousSlide.length ? $previousSlide : $el.nextAll('.carousel-item').last()
  }

  // Update active slide indicators
  function switchIndicator(index) {
    $carouselIndicators.children().each(function(i, el) {
      $(el).toggleClass('active', i === index)
    })
  }

  $slides.each(function (i, el) {
    var $slide = $(el)

    // Populate carousel indicators list
    $('<li>')
      .attr('data-target', '#carousel-wrapper-header')
      .attr('data-slide-to', i)
      .toggleClass('active', i === 0)
      .appendTo($carouselIndicators)

    // Convert the provided image tag into background image styles.
    var $img = $slide.find('img')
      , $nextImg = nextSlide($slide).find('img')
      , img_src = $img.get(0).currentSrc || $img.attr('src')
    $slide
      .css('background-image', 'url(' + img_src + ')')
      .css('background-position', $img.data('background-position'))

    // Add an element within the slide to hold the next slide preview.
    var $preview = $('<div>')
      .addClass('carousel-preview-wrap')
      .prependTo($slide)

    var next_img_src = $nextImg.get(0).currentSrc || $nextImg.attr('src')
    $('<div>')
      .addClass('carousel-preview')
      .css('background-image', 'url(' + next_img_src + ')')
      .css('background-position', $nextImg.data('background-position'))
      .appendTo($preview)

    // Populate carousel slide index
    $slide.attr('data-slide', i)
  })

  /**
  * Advance to the next slide in the carousel.
  */
  function advanceCarousel() {
    var $active = $slides.filter('.active')
      , $next = nextSlide($active).addClass('next')

    if (activeTransition) {
      // A transition is in progress, so proceed to the next pair of slides
      clearTimeout(activeTransition)
      activeTransition = null
      $slides.removeClass('fade-out slide-over active next')
      $next.addClass('active')
      nextSlide($next).addClass('next')
      advanceCarousel()
      return
    }

    // Transition out the active slide
    $active.addClass('slide-over')

    switchIndicator($slides.index($next))

    // When transition is done, swap out the slides
    activeTransition = setTimeout(function beginTransition() {
      $active.addClass('fade-out')
      activeTransition = setTimeout(function completeTransition() {
        $active.removeClass('active')
        $slides.removeClass('slide-over fade-out')
        $next.removeClass('next').addClass('active')
        // Ensure the new upcoming slide has .next
        nextSlide($next).addClass('next')
        activeTransition = null
      }, SLIDE_TRANSITION_SPEED / 2)
    }, SLIDE_TRANSITION_SPEED)
  }

  function backwardsCarousel() {
    var $active = $slides.filter('.active')
      , $previous = previousSlide($active)
    activate($previous.data('slide'))
  }

  /**
  * Switch to a specific slide.
  *
  * @param {Number} slideIndex The index of a slide in the carousel.
  */
  function activate(slideIndex) {
    var $slide = $slides.eq(slideIndex)

    if ($slide.hasClass('active') && !$slide.hasClass('slide-over')) {
      // If the requested slide is active and not transitioning, do nothing.
      return
    }

    if ($slide.hasClass('next')) {
      // If the slide being requested is next, transition normally.
      advanceCarousel()
      return
    }

    if (activeTransition) {
      clearTimeout(activeTransition)
    }

    switchIndicator(slideIndex)

    $slides.removeClass('active next slide-over fade-out')
    $slide.addClass('active')
    nextSlide($slide).addClass('next')
  }

  // Bind mouse interaction events
  $headerCarousel.on('click', '.carousel-control-next', function(evt) {
    evt.preventDefault()
    advanceCarousel()
  }).on('click', '.carousel-indicators li', function (evt) {
    evt.preventDefault()
    activate($(evt.target).data('slide-to'))
  })


  /* Carousel header swipe on mobile */
  if($('.carousel-header').length > 0) {
    var carousel_element = $('.carousel-header')[0]
      , carousel_head_hammer = new Hammer(carousel_element, { recognizers: [] })
      , hammer = new Hammer.Manager(carousel_head_hammer.element)
      , swipe = new Hammer.Swipe()

    hammer.add(swipe)

    hammer.on('swipeleft', function(){
      advanceCarousel()
    })

    hammer.on('swiperight', function(){
      backwardsCarousel()
    })

    // Vertical swiping on carousel should scroll the page
    hammer.on('swipeup', function(event){
      var y = $(window).scrollTop()
      event.preventDefault()
      $('html, body').animate({scrollTop: y + 200})
    })
    hammer.on('swipedown', function(event){
      var y = $(window).scrollTop()
      event.preventDefault()
      $('html, body').animate({scrollTop: y - 200})
    })
  }
})
