/* global dismissDashboardNotice */

jQuery('#p4-notice').on('click', '.notice-dismiss', () => {
  jQuery.post(dismissDashboardNotice.ajaxurl, {'action': 'dismiss_dashboard_notice'}, () => {
    jQuery('#p4-notice').hide();
  });
});
