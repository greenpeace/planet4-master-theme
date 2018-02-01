$(document).ready(function () {

	// Click event listener for categories select box.
	// If a category is chosen that is also a p4-page-type, then change also the p4-page-type attribute of the page.
	$('#categorychecklist input[type=checkbox]').on("change", function () {
		var category_name = $.trim($(this).parent().text()).toLowerCase();
		var p4_page_types = $('select[name="p4-page-type"] option').map(
			function () {
				return $.trim($(this).text()).toLowerCase();
			}).get().filter(e => 'none' !== e);

		if ($(this).prop("checked") === true) {
			if ($.inArray(category_name.toLowerCase(), p4_page_types) > -1) {
				var select_value = category_name.split(" ").join("-");
				$("select[name='p4-page-type']").val(select_value);
			}
		}
	});
});
