/* global dismissDashboardNotice */

const p4Notice = document.getElementById('p4-notice');
if (p4Notice) {
  p4Notice.addEventListener('click', event => {
    if (event.target.classList.contains('notice-dismiss')) {
      fetch(dismissDashboardNotice.ajaxurl, {
        method: 'POST',
        body: new URLSearchParams({action: 'dismiss_dashboard_notice'}),
      })
        .then(response => {
          if (response.ok) {
            p4Notice.style.display = 'none';
          } else {
            console.error(response.statusText); //eslint-disable-line no-console
          }
        })
        .catch(err => {
          console.error(err); //eslint-disable-line no-console
        });
    }
  });
}
