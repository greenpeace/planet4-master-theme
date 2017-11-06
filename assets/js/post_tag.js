$ = jQuery;

$(document).ready(function() {

	/**
	 * Taxonomy_Image
	 */
	$("#insert_image_tag_button").off("click").on("click", function () {
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
					var $selected_image = media_modal.state().get("selection").first().toJSON();
					$("#tag_attachment_id").val($selected_image.id);
					$("#tag_attachment").val($selected_image.url);
					$(".add-wrap .attachment-thumbnail").attr("src", $selected_image.sizes.thumbnail.url);
					$(".edit-wrap .attachment-thumbnail").attr("src", $selected_image.url);
					$(".dashicons-dismiss:hidden").show();
				})
				.open();
		}

		return false;
	});

	$(".form-field .attachment-thumbnail").off("click").on("click", function () {
		$("#insert_image_tag_button").click();
	});

	// Clean up the custom fields, since the taxonomy save is made via ajax and the taxonomy page does not reload.
	$("#submit", $("#addtag")).off("click").on("click", function () {
		setTimeout(function () {
			$("#tag_attachment_id, #tag_attachment").val("");
			$(".form-field .attachment-thumbnail").attr("src", "");
		}, 300);
	});

	$(".dashicons-dismiss").off("click").on("click", function () {
		$("#tag_attachment_id").val(0);
		$("#tag_attachment").val("");
		$(".form-field .attachment-thumbnail").attr("src", "");
		$(this).hide();
	} );
} );
