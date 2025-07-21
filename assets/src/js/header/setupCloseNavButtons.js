/**
 * Close navigation element when clicking on specific buttons.
 *
 * @param {event}  event       Click event.
 * @param {string} buttonClass Button class that was clicked on.
 */
const closeNavElement = (event, buttonClass) => {
  event.preventDefault();
  const closeButton = document.querySelector(buttonClass);
  closeButton.click();
};

/**
 * Close nav elements if the user clicks on the corresponding close buttons.
 */
export default () => {
  const closeNavbarButton = document.querySelector('.close-navbar-dropdown');
  const closeNavMenuButton = document.querySelector('.nav-menu-close');

  if (!closeNavbarButton && !closeNavMenuButton) {
    return;
  }

  if (closeNavbarButton) {
    closeNavbarButton.onclick = event => closeNavElement(event, '.navbar-dropdown-toggle');
  }

  if (closeNavMenuButton) {
    closeNavMenuButton.onclick = event => closeNavElement(event, '.nav-menu-toggle');
  }
};
