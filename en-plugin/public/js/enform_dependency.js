jQuery(function ($) {
  'use strict';

  $('.en__field__input--checkbox').change(function() {
    let dependency = $(this).attr('data-dependency');
    let $el        = $('.dependency-'+dependency);

    if (dependency) {
      if (this.checked) {
        $el.removeAttr('disabled');
        $el.parent().removeClass('disable-checkbox');
      } else {
        $el.attr('disabled', 'disabled');
        $el.prop('checked', false);
        $el.parent().addClass('disable-checkbox');
      }
    }
  });
});
