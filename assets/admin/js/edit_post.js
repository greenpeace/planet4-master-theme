$(document).ready(function () {


	function remove_categories(categories_array, category_name) {
		var categories_to_be_removed = categories_array.filter(e => e.category !== category_name);
		categories_to_be_removed.forEach(function (category) {
			$("#categorychecklist input[id="+category.id+"]").prop('checked', false);
		});
	}

	// Populate array with planet4 page types
	var p4_page_types = $('select[name="p4-page-type"] option').map(
		function () {
			return $.trim($(this).text()).toLowerCase();
		}).get().filter(e => 'none' !== e);

	// Populate array with categories that are also planet4 page types
	var categories_array = $('#categorychecklist input[type=checkbox]').map(
		function () {
			var category = $.trim($(this).parent().text()).toLowerCase();
			var category_id = $.trim($(this).attr('id'));
			if (p4_page_types.includes(category)) {
				return {id: category_id, category: category}
			}
		}).get();

	// Click event listener for categories select box.
	// If a category is chosen that is also a p4-page-type, then change also the p4-page-type attribute of the page.
	$('#categorychecklist input[type=checkbox]').on("change", function () {
		var category_name = $.trim($(this).parent().text()).toLowerCase();

		if ($(this).prop("checked") === true) {
			if (p4_page_types.includes(category_name) ) {
				var select_value = category_name.split(" ").join("-");
				$("select[name='p4-page-type']").val(select_value);
				remove_categories(categories_array, category_name);
			}
		}
	});

	// Change event listener for planet4 page type select box.
	// Select the proper category when a planet4 page type is selected and deselect the rest categories that are
	// planet4 page types.
	$("select[name='p4-page-type']").on("change", function () {
		var page_type = $.trim($(this).find(":selected").text()).toLowerCase();
		if (page_type === 'none') {
			return;
		}

		remove_categories(categories_array, page_type);
		categories_array.forEach(function (category) {
			if (page_type === category.category) {
				$("input[id="+category.id+"]").prop('checked', true);
			}
		});
	});

});
