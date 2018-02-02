$(document).ready(function () {

	// Populate array with planet4 page types if it is not defined from the backend.
	if (p4_page_types === undefined) {
		var p4_page_types = ["press release", "publication", "story"];
	}

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
			if (p4_page_types.includes(category_name)) {
				remove_categories(categories_array, category_name);
			}
		}
	});

	function remove_categories(categories_array, category_name) {
		var categories_to_be_removed = categories_array.filter(e = > e.category !== category_name);
		categories_to_be_removed.forEach(function (category) {
			$("#categorychecklist input[id=" + category.id + "]").prop('checked', false);
		});
	}
});
