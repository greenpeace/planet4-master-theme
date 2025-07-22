/**
 * Spoof click on nav menu toggle when clicking on nav menu close button.
 */
export default () => {
  const closeNavMenuButton = document.querySelector('.nav-menu-close');
  const closeButton = document.querySelector('.nav-menu-toggle');

  if (!closeNavMenuButton || !closeButton) {
    return;
  }

  closeNavMenuButton.onclick = event => {
    event.preventDefault();
    closeButton.click();
  };
};
