$(document).ready(function () {
  'use strict';

  // Parse p4_page_type passed to a variable server-side.
  if (p4_page_type_mapping !== undefined) {
    try {
      p4_page_type_mapping = JSON.parse(p4_page_type_mapping);
    } catch (e) {
      p4_page_type_mapping = [];
    }
  }

  // Remove/uncheck categories that are mapped to planet4 page types.
  function remove_categories(categories_array, category_name) {
    var categories_to_be_removed = categories_array.filter(function (e) {
      return e.category !== category_name;
    });
    categories_to_be_removed.forEach(function (category) {
      $('#categorychecklist input[value=' + category.id + ']').prop('checked', false);
    });
  }

  // Populate array with categories that are also planet4 page types.
  var categories_array = $('#categorychecklist input[type=checkbox]').map(
    function () {
      var category = $.trim($(this).parent().text()).toLowerCase();
      var category_id = parseInt($.trim($(this).attr('value')));
      if (undefined !== p4_page_type_mapping && Array.isArray(p4_page_type_mapping)) {
        if (p4_page_type_mapping.map(function (e) {return e.category_id;}).includes(category_id)) {
          return {id: category_id, category: category};
        }
      }
    }).get();

  // Click event listener for categories select box.
  // If a category is chosen that is also a p4-page-type, then change also the p4-page-type attribute of the page.
  $('#categorychecklist input[type=checkbox]').on('change', function () {
    var category_name = $.trim($(this).parent().text()).toLowerCase();
    var category_id = parseInt($.trim($(this).attr('value')));
    if (undefined !== p4_page_type_mapping && Array.isArray(p4_page_type_mapping)) {
      if (p4_page_type_mapping.map(function (e) {return e.category_id;}).includes(category_id)) {
        if ($(this).prop('checked') === true) {
          var select_value = p4_page_type_mapping.filter(function (e) {return e.category_id == category_id;});
          $('select[name="p4-page-type"]').val(select_value[0].p4_page_type_slug);
          remove_categories(categories_array, category_name);
        } else {
          $('select[name="p4-page-type"]').val('-1');
        }
      }
    }
  });

  // Custom handling of Featured Image selection, so that we know if Editor tried to override the
  // auto-selected first image found within the Post's content.
  $("#postimagediv").off("click", "#set-post-thumbnail").on("click", "#set-post-thumbnail", function () {
  	if ( typeof wp !== "undefined" ) {

  		var media_modal = wp.media({
			title: localizations.media_title,
			library: {
				type: "image"
			},
			multiple: false
		});

  		media_modal
			.on("select", function () {
				var selected_image = media_modal.state().get("selection").first().toJSON();

				// TODO - There might be a prettier way to override the modal's default behavior when selecting Image.
				$("#set-post-thumbnail").parent().remove();
				$("#postimagediv .inside")
					.prepend('<p class="hide-if-no-js"><a href="' + window.location.href + '/wp-admin/media-upload.php?post_id=' + selected_image.id + '"&amp;type=image&amp;TB_iframe=1" id="set-post-thumbnail" aria-describedby="set-post-thumbnail-desc" class="thickbox"><img width="169" height="266" src="' + selected_image.url + '" class="attachment-266x266 size-266x266" alt=""></a></p>')
					.append('<input type="hidden" name="user_set_featured_image" value="true" />');
				$('#_thumbnail_id').val( selected_image.id );
				$("input[name=user_removed_featured_image]").remove();
			})
			.open();
  	}

  	return false;
  }).on("click", "#remove-post-thumbnail", function () {
	  $("#postimagediv").append('<input type="hidden" name="user_removed_featured_image" value="true" />');
	  $("input[name=user_set_featured_image]").remove();
  });
});
