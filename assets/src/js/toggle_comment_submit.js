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

  const turnstile = document.querySelector('#turnstile-container');
  let turnstileValid = !turnstile; // true if no Turnstile present

  const toggleSubmit = () => {
    const isChecked = checkbox.checked;
    const enabled = isChecked && turnstileValid;

    submit.disabled = !enabled;
    submit.setAttribute('aria-disabled', !enabled);
  };

  if (turnstile) {
    window.turnstileToggleCommentSubmit = isValid => {
      turnstileValid = isValid;
      toggleSubmit();
    };
  }

  checkbox.addEventListener('change', toggleSubmit);
  toggleSubmit();
});
