/* global metaboxSearchData */

$ = jQuery;

$('#parent_id').off('change').on('change', function() {
  // Check selected Parent page and give bigger weight if it will be an Action page
  if ((metaboxSearchData.act_page ?? -1) === $(this).val()) {
    $('#weight').val(metaboxSearchData.action_weight);
  } else {
    $('#weight').val(metaboxSearchData.page_weight);
  }
});
