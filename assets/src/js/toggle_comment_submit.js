document.addEventListener('DOMContentLoaded', () => {
  const checkbox = document.querySelector('#gdpr-comments-checkbox');
  const submit = document.querySelector('#commentform button[type="submit"]');

  if (!checkbox || !submit) {
    return;
  }

  const toggleSubmit = () => {
    const isChecked = checkbox.checked;
    submit.disabled = !isChecked;
    submit.setAttribute('aria-disabled', !isChecked);
  };

  checkbox.addEventListener('change', toggleSubmit);
  toggleSubmit();
});
