export const setImageTitlesFromAltText = function($) {
  'use strict';

  $('.page-template img, .post-content img').each( function() {
    $(this).attr('title', $(this).attr('alt') );
  });
};

export const setBlueLinkStyles = function($) {
  console.log('here!!');
  $('a').each( function() {
    if( $( this ).css('color') === 'rgb(0, 109, 253)') {
      $( this ).addClass("blue-link");
    }
  });
}