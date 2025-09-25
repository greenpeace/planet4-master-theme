/**
 * Handles enabling/disabling the comment form submit button
 * based on GDPR checkbox state and Cloudflare Turnstile validation.
 */
document.addEventListener('DOMContentLoaded', () => {
  const checkbox = document.querySelector('#gdpr-comments-checkbox');
  const submit = document.querySelector('#commentform button[type="submit"]');

  if (!checkbox || !submit) {
    return;
  }

  let turnstileValid = false;

  const toggleSubmit = () => {
    const isChecked = checkbox.checked;
    const enabled = isChecked && turnstileValid;

    submit.disabled = !enabled;
    submit.setAttribute('aria-disabled', !enabled);
  };

  checkbox.addEventListener('change', toggleSubmit);

  window.ToggleCommentSubmit = function(isValid) {
    turnstileValid = isValid;
    toggleSubmit();
  };

  toggleSubmit();
});
