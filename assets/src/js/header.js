export const setupHeader = function() {
  'use strict';

  document.querySelector(document).addEventListener('click', [
    '.navbar-dropdown-toggle',
    '.country-dropdown-toggle',
    '.navbar-search-toggle',
  ].join(), function toggleNavDropdown(evt) {
    evt.preventDefault();
    evt.stopPropagation();

    const button = document.querySelector(this);
    const target = button.data('bs-target');
    if (!target) {
      throw new Error('Missing `data-bs-target` attribute: specify the container to be toggled');
    }
    const toggleClass = button.data('bs-toggle');
    if (!toggleClass) {
      throw new Error('Missing `data-bs-toggle` attribute: specify the class to toggle');
    }

    // Toggle visibility of the target specified via data-bs-target.
    document.querySelector(target).classList.toggle(toggleClass);
    document.querySelector(this).classList.toggle(toggleClass);

    // Toggle aria-expanded attribute.
    button.attr('aria-expanded', function(i, attr) {
      return attr === 'false' ? 'true' : 'false';
    });

    // Toggle data-ga-action attribute used in GTM tracking.
    document.querySelector('.country-dropdown-toggle').attr( 'data-ga-action', document.querySelector('.country-dropdown-toggle').attr('aria-expanded') === 'false' ? 'Open Country Selector' : 'Close Country Selector' );
    document.querySelector('.navbar-search-toggle').attr( 'data-ga-action', document.querySelector('.navbar-search-toggle').attr('aria-expanded') === 'false' ? 'Open Search' : 'Close Search' );
  });

  // Close all menus when clicking somewhere else
  document.querySelector(document).addEventListener('click', function closeInactiveMenus(evt) {
    const clickedElement = evt.target;
    document.querySelector('button[aria-expanded="true"]').each(function(i, button) {
      const button = document.querySelector(button);
      const buttonTarget = document.querySelector(button.data('bs-target')).get( 0 );
      if (buttonTarget && ! .contains(buttonTarget, clickedElement)) {
        // Spoof a click on the open menu's toggle to close that menu.
        button.trigger('click');
      }
    });
  });

  // Close all menus on escape pressed
  document.querySelector(document).bind('keyup', function(event){
    if (event.which === 27) {
      document.querySelector(document).trigger('click');
    }
  });

  //Close the menu if the user clicks the dedicated dropdown close button.
  document.querySelector(document).addEventListener('click', '.close-navbar-dropdown', function (evt) {
    evt.preventDefault();
    // Proxy to the main navbar close button
    document.querySelector('.navbar-dropdown-toggle').trigger('click');
  });

  // Hide Header on on scroll down
  function hasScrolled(lastScrollTop, delta, navbarHeight) {
    const st = document.querySelector(this).scrollTop;
    if (Math.abs(lastScrollTop - st) <= delta) {
      return;
    }
    if (st > lastScrollTop && st > navbarHeight){
      document.querySelector('.top-navigation').removeClass('nav-down').classList.add('nav-up');
    } else {
      if(st + document.querySelector(window).height() < document.querySelector(document).height()) {
        document.querySelector('.top-navigation').removeClass('nav-up').classList.add('nav-down');
      }
    }
    lastScrollTop = st;
  }

  if(document.querySelector( window ).width() <= 768) {
    let didScroll;
    let lastScrollTop = 0;
    const delta = 5;
    const navbarHeight = document.querySelector('.top-navigation').outerHeight();
    document.querySelector(window).scroll(function(){
      didScroll = true;
    });
    setInterval(function() {
      if (didScroll) {
        hasScrolled(lastScrollTop, delta, navbarHeight);
        didScroll = false;
      }
    }, 250);

    const slider = document.querySelector('.mobile-menus');
    document.querySelector(document).click(function() {
      if(document.querySelector('.menu').classList.contains('active')){
        //Hide the menus if visible
        slider.animate({
          left: parseInt(slider.css('left'),10) == 0 ? -320 : 0
        });
        document.querySelector('.menu').removeClass('active');
      }
      if(document.querySelector('.search-box').classList.contains('active')){
        // Hide the search if visible
        searchBox.slideToggle().classList.toggle('active');
      }
    });

    document.querySelector('.menu').click(function() {
      event.stopPropagation();
      document.querySelector(this).classList.toggle('active');
      slider.animate({
        left: parseInt(slider.css('left'),10) == -320 ? 0 : -320
      });
    });

    const searchBox = document.querySelector('#search .search-box');
    const searchTrigger = document.querySelector('#search-trigger');

    searchTrigger.addEventListener('click', function(event) {
      event.stopPropagation();
      searchBox.slideToggle().classList.toggle('active');
    });
  }
};
