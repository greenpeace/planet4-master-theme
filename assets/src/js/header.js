import {setupAccessibleNavMenu} from './header/setupAccessibleNavMenu';
import setupMobileTabsMenuScroll from './header/setupMobileTabsMenuScroll';
import {setupCloseNavMenuButton, setupDocumentClick, toggleNavElements} from './header/setupNavMenu';
import {setupTransparentNavHomepage} from './header/setupTransparentNavHomepage';
import {setupNavInteractions} from './header/setupNavInteractions';
import {setupSkipLinksFocus} from './header/setupSkipLinksFocus';
import {setupDynamicHambMenu} from './header/setupDynamicHambMenu';

export const setupHeader = () => {
  // Set the mobile tabs menu behavior on scroll.
  setupMobileTabsMenuScroll();

  // Close navbar elements when clicking outside of menu.
  setupDocumentClick();

  // Spoof click on nav menu toggle when clicking on nav menu close button.
  setupCloseNavMenuButton();

  // Setup keyboard accessibility in the navigation menu.
  setupAccessibleNavMenu();

  // Handle clicking on navigation elements.
  toggleNavElements();

  // Handles the transition to the transparent styles for the Navigation Menu.
  setupTransparentNavHomepage();

  // Set up other nav interactions and events.
  setupNavInteractions();

  // Set up changing the focused element when a Skip Link is clicked.
  setupSkipLinksFocus();

  // Set up displaying the hamburger menu when the desktop menu is too large.
  setupDynamicHambMenu();
};
