export const setupHeader = function($) {
  'use strict';

  $(document).on('click', [
    '.navbar-dropdown-toggle',
    '.nav-menu-toggle',
    '.country-dropdown-toggle',
    '.country-selector-toggle',
    '.navbar-search-toggle',
    '.nav-search-toggle',
    '.nav-languages-toggle',
  ].join(), function toggleNavDropdown(evt) {
    evt.preventDefault();
    evt.stopPropagation();

    const $button = $(this);
    const target = $button.data('bs-target');
    if (!target) {
      throw new Error('Missing `data-bs-target` attribute: specify the container to be toggled');
    }
    const toggleClass = $button.data('bs-toggle');
    if (!toggleClass) {
      throw new Error('Missing `data-bs-toggle` attribute: specify the class to toggle');
    }

    // Toggle visibility of the target specified via data-bs-target.
    $(target).toggleClass(toggleClass);
    $(this).toggleClass(toggleClass);

    // Toggle aria-expanded attribute.
    $button.attr('aria-expanded', (i, attr) => {
      return attr === 'false' ? 'true' : 'false';
    });

    // Toggle data-ga-action attribute used in GTM tracking.
    $('.country-dropdown-toggle').attr( 'data-ga-action', $('.country-dropdown-toggle').attr('aria-expanded') === 'false' ? 'Open Country Selector' : 'Close Country Selector' );
    $('.country-selector-toggle').attr( 'data-ga-action', $('.country-selector-toggle').attr('aria-expanded') === 'false' ? 'Open Country Selector' : 'Close Country Selector' );
    $('.navbar-search-toggle').attr( 'data-ga-action', $('.navbar-search-toggle').attr('aria-expanded') === 'false' ? 'Open Search' : 'Close Search' );
    $('.nav-search-toggle').attr( 'data-ga-action', $('.navbar-search-toggle').attr('aria-expanded') === 'false' ? 'Open Search' : 'Close Search' );
  });

  // Close all menus when clicking somewhere else
  $(document).on('click', function closeInactiveMenus(evt) {
    const clickedElement = evt.target;
    $('button[aria-expanded="true"]').each((i, button) => {
      const $button = $(button);
      const buttonTarget = $($button.data('bs-target')).get( 0 );
      if (buttonTarget && ! $.contains(buttonTarget, clickedElement)) {
        // Spoof a click on the open menu's toggle to close that menu.
        $button.trigger('click');
      }
    });
  });

  // Close all menus on escape pressed
  $(document).bind('keyup', (event) => {
    if (event.which === 27) {
      $(document).trigger('click');
    }
  });

  $(document).on('click', '.nav-search-toggle', (evt) => {
    evt.preventDefault();
    if (evt.currentTarget.getAttribute('aria-expanded') === 'true') {
      $('#search_input').focus();
    }
  });

  //Close the menu if the user clicks the dedicated dropdown close button.
  $(document).on('click', '.close-navbar-dropdown', (evt) => {
    evt.preventDefault();
    // Proxy to the main navbar close button
    $('.navbar-dropdown-toggle').trigger('click');
  });

  //Close the menu if the user clicks the dedicated dropdown close button.
  $(document).on('click', '.nav-menu-close', (evt) => {
    evt.preventDefault();
    // Proxy to the main navbar close button
    $('.nav-menu-toggle').trigger('click');
  });

  // Hide Header on on scroll down
  function hasScrolled(lastScrollTop, delta, navbarHeight) {
    const st = $(this).scrollTop();
    if (Math.abs(lastScrollTop - st) <= delta) {
      return;
    }
    if (st > lastScrollTop && st > navbarHeight){
      $('.top-navigation').removeClass('nav-down').addClass('nav-up');
    } else {
      if(st + $(window).height() < $(document).height()) {
        $('.top-navigation').removeClass('nav-up').addClass('nav-down');
      }
    }
    lastScrollTop = st;
  }

  if($( window ).width() <= 768) {
    let didScroll;
    let lastScrollTop = 0;
    const delta = 5;
    const navbarHeight = $('.top-navigation').outerHeight();
    $(window).scroll(() => {
      didScroll = true;
    });
    setInterval(() => {
      if (didScroll) {
        hasScrolled(lastScrollTop, delta, navbarHeight);
        didScroll = false;
      }
    }, 250);

    const $slider = $('.mobile-menus');
    $(document).click(() => {
      if($('.menu').hasClass('active')){
        //Hide the menus if visible
        $slider.animate({
          left: parseInt($slider.css('left'),10) == 0 ? -320 : 0
        });
        $('.menu').removeClass('active');
      }
      if($('.search-box').hasClass('active')){
        // Hide the search if visible
        $searchBox.slideToggle().toggleClass('active');
      }
    });

    $('.menu').click(function() {
      event.stopPropagation();
      $(this).toggleClass('active');
      $slider.animate({
        left: parseInt($slider.css('left'),10) == -320 ? 0 : -320
      });
    });

    const $searchBox = $('#search .search-box');
    const $searchTrigger = $('#search-trigger');

    $searchTrigger.on('click', (event) => {
      event.stopPropagation();
      $searchBox.slideToggle().toggleClass('active');
    });
  }
};
