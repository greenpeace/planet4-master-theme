document.addEventListener('DOMContentLoaded', () => {
  const checkbox = document.querySelector('#gdpr-comments-checkbox');
  const submit = document.querySelector('#commentform button[type="submit"]');

  if (!checkbox || !submit) {
    return;
  }

  const toggleSubmit = () => { submit.disabled = !checkbox.checked; };

  checkbox.addEventListener('change', toggleSubmit);
  toggleSubmit();
});
