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

  // Handle GDPR checkbox
  checkbox.addEventListener('change', toggleSubmit);

  // Expose callback for Turnstile (called from PHP inline script)
  window.ToggleCommentSubmit = function(isValid) {
    turnstileValid = isValid;
    toggleSubmit();
  };

  // Run once on page load
  toggleSubmit();
});
