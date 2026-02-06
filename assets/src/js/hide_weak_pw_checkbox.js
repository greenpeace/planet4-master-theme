/**
 * - Automatically checks and hides the "Confirm use of weak password" checkbox.
 * - Validates password input length (must be at least 4 characters).
 * - Enables/disables the submit button(s) based on password validity.
 */
document.addEventListener('DOMContentLoaded', () => {
  const passwordField = document.getElementById('pass1');

  if (!passwordField) {return;}

  const customValidation = () => {
    const weakCheckbox = document.querySelector('.pw-weak');
    if (weakCheckbox) {
      weakCheckbox.querySelector('.pw-checkbox').checked = true;
      weakCheckbox.style.display = 'none !important';
      weakCheckbox.style.visibility = 'hidden !important';
      weakCheckbox.style.opacity = '0 !important';
      weakCheckbox.dispatchEvent(new Event('change'));
    }

    const valid = passwordField.value.trim().length >= 4;
    const submitButtons = document.querySelectorAll('p.submit input[type=submit]');

    submitButtons.forEach(btn => {
      btn.disabled = !valid;
    });
  };

  passwordField.addEventListener('input', customValidation);
  passwordField.addEventListener('change', customValidation);
});
