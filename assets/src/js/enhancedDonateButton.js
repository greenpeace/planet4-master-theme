export const setupEnhancedDonateButton = function() {
  function isMobile() {
    return window.matchMedia('(max-width: 576px)').matches;
  }

  function setupDonateButton() {
    if ( isMobile() ) {
      if (!$('.btn-enhanced-donate.btn-donate-top').length) {
        $('.btn-enhanced-donate').clone().appendTo('body').addClass('btn-donate-top');
        $('.btn-donate-top').parent('body').addClass('with-donate-on-top');
      }
    } else {
      $('.btn-donate-top').remove();
      $('body').removeClass('with-donate-on-top');
    }
  }

  jQuery(function($) {
    setupDonateButton();

    $( window ).on('resize', function() {
      setupDonateButton();
    });

    const windowHeight = window.innerHeight;

    window.addEventListener('scroll', function() {
      if ( isMobile() ) {
        $('.btn-enhanced-donate.btn-donate-top').toggleClass('btn-donate-top-hide', window.scrollY > windowHeight);
      } else {
        $('.btn-enhanced-donate.btn-donate-top').removeClass('btn-donate-top-hide');
      }
    });
  });
};
