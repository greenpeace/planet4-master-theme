/* global wp, localizations */

$ = jQuery; //eslint-disable-line no-global-assign

$(document).ready(function() {

  /**
  * Taxonomy_Image
  */
  $('.insert-media').off('click').on('click', function () {
    if ( typeof wp !== 'undefined' ) {
      const field      = $(this).closest('.form-field');
      const add_field  = $(this).closest('.add-wrap');
      const edit_field = $(this).closest('.edit-wrap');

      const media_modal = wp.media({
        title: localizations.media_title,
        library: {
          type: 'image'
        },
        multiple: false
      });

      media_modal.on('select', function () {
        const $selected_image = media_modal.state().get('selection').first().toJSON();
        $('.field-id', field).val($selected_image.id);
        $('.field-url', field).val($selected_image.url);
        $('.attachment-thumbnail', add_field).attr('src', $selected_image.sizes.thumbnail.url);
        $('.attachment-thumbnail', edit_field).attr('src', $selected_image.url);
        $('.dashicons-dismiss:hidden', field).show();
      }).open();
    }

    return false;
  });

  $('.form-field .attachment-thumbnail').off('click').on('click', function () {
    $('.insert-media', $(this).parent()).click();
  });

  // Clean up the custom fields, since the taxonomy save is made via ajax and the taxonomy page does not reload.
  $('#submit', $('#addtag')).off('click').on('click', function () {
    setTimeout(function () {
      $('#tag_attachment_id, #tag_attachment').val('');
      $('.form-field .attachment-thumbnail').attr('src', '');
    }, 300);
  });

  $('.dashicons-dismiss').off('click').on('click', function () {
    const field = $(this).closest('.form-field');

    $('.field-id', field).val(0);
    $('.field-url', field).val('');
    $('.attachment-thumbnail', field).attr('src', '');
    $(this).hide();
  } );
} );
