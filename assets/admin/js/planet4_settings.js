jQuery(document).ready(function () {


	$("select[name^=p4_page_type_]").on("change", function () {
		var p4_page_type_mapping = populate_mapping_field();
		$('#p4-page-types-mapping').val(JSON.stringify(p4_page_type_mapping));
	});

	function populate_mapping_field() {

		var result = [];
		$('select[name^=p4_page_type_]').each(function () {
			var slug = $(this).attr("name");
			slug = slug.replace('p4_page_type_', '').replace('_category', '');
			var category_slug = $.trim($(this).find(':selected').val());

			var p4_page_type_id = [];
			if (undefined !== p4_page_types) {
				// Variable p4_page_types is passed from backend.
				p4_page_type_id = p4_page_types.filter(function (e) {
					return e.slug == slug;
				});
			}
			var category_id = [];
			if (undefined !== categories) {
				// Variable categories is passed from backend.
				category_id = categories.filter(function (e) {
					return e.slug == category_slug;
				});
			}
			p4_page_type_id = p4_page_type_id.length === 0 ? '' : p4_page_type_id[0].term_id;
			category_id = category_id.length === 0 ? '' : category_id[0].term_id;
			result.push({
				p4_page_type_id: p4_page_type_id,
				p4_page_type_slug: slug,
				category_slug: category_slug,
				category_id: category_id
			})
		});
		return result;
	}
});
