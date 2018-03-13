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
});
