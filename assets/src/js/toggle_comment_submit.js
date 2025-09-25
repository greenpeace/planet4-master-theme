/**
 * Handles enabling/disabling the comment form submit button
 * based on GDPR checkbox state and Cloudflare Turnstile validation.
 */
document.addEventListener('DOMContentLoaded', () => {
  const checkbox = document.querySelector('#gdpr-comments-checkbox');
  const submit = document.querySelector('#commentform button[type="submit"]');

  if (!checkbox || !submit) {return;}

  const turnstile = document.querySelector('.cf-turnstile');
  let turnstileValid = true;

  if (turnstile) {
    turnstileValid = false;
    window.ToggleCommentSubmit = function(isValid) {
      turnstileValid = isValid;
      toggleSubmit();
    };
  }

  const toggleSubmit = () => {
    const isChecked = checkbox.checked;
    const enabled = isChecked && turnstileValid;

    submit.disabled = !enabled;
    submit.setAttribute('aria-disabled', !enabled);
  };

  checkbox.addEventListener('change', toggleSubmit);

  toggleSubmit();
});
