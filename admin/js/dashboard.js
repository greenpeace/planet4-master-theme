/* global ajaxurl */

// Dashboard page.
$(document).ready(function() {
  'use strict';

  $('.btn-cp-action').off('click').on('click', function (event) {
    if ( $(this).data('action') ) {  // Added this check in order to allow to do a usual page navigation instead of an ajax request.
      event.preventDefault();

      const $btn = $(this);
      const $response = $('.cp-subitem-response', $btn.parent());
      const confirmation_text = $btn.data('confirm');
      let answer;

      if (confirmation_text) {
        answer = confirm(confirmation_text);
        if ( ! answer) {
          return;
        }
      }

      $.ajax({
        url: ajaxurl,
        type: 'GET',
        data: {
          action: $btn.data('action'),
          'cp-action': $btn.data('action'),
          '_wpnonce': $('#_wpnonce').val()
        },
        dataType: 'json'
      }).done(function (response) {
        if (response.message) {
          $response.hide().removeClass('cp-error cp-success');
          $response.text(response.message);
          if (response.class) {
            $response.addClass(response.class);
          }
          $response.show('slow');
        }
      });
    }
  });
});
