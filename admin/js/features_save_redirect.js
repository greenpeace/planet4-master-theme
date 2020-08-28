document.addEventListener('DOMContentLoaded', () => {
  const savedMessage = document.querySelector('#setting-error-settings_updated.notice-success');
  if (savedMessage) {
    savedMessage.innerHTML = savedMessage.innerHTML + ' Reloading page to apply feature changes.';
    // Don't use window.location.reload as we want to GET instead of POST.
    // eslint-disable-next-line no-self-assign
    window.location.href = window.location.href;
  }
});
