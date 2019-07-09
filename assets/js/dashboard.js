/* global ajaxurl */

$ = jQuery; //eslint-disable-line no-global-assign

// Dashboard page.
$(function() {
  $('.btn-cp-action').off('click').on('click', function (event) {
    if ( $(this).data('action') ) {  // Added this check in order to allow to do a usual page navigation instead of an ajax request.
      event.preventDefault();

      var $btn = $(this);
      var $response = $('.cp-subitem-response', $btn.parent());
      var confirmation_text = $btn.data('confirm');
      var answer;

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
