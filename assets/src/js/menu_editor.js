const {__, sprintf} = wp.i18n;

/**
 * Check main navigation menu for restrictions on:
 * - number of items and sub-items
 * - title length
 * Limit maximum depth of menu
 * Add indication for creating submenu
 *
 * Expected p4_menu_config:
 * {
 *  <location-name>: {
 *    maxDepth: int, // 0 for flat menu
 *    maxItems: int, // Max number of children items in root or sub-item
 *    maxChars: int, // Max number of characters in item title
 *  },
 *  <location-name-2>: {
 *    maxDepth: int,
 *    depthConf: {
 *      <depth level>: {
 *        maxItems: int, // Max number of children items in each parent of this level
 *        maxChars: int, // Max number of characters in item title of this level
 *      }
 *    }
 *  },
 * }
 */
const menuEditorRestrictions = () => {
  /* global p4_menu_config */
  if (!p4_menu_config) {
    return;
  }

  // Get divs to check and write in
  const menuContent = document.getElementById('menu-to-edit');
  const menuEditorFooter = document.getElementById('nav-menu-footer');

  // Add instruction for submenus
  const instruction = document.createElement('p');
  instruction.textContent = __(
    'To add a sub-item, drag it slightly to the side.',
    'planet4-master-theme-backend'
  );
  document.querySelector('.drag-instructions.post-body-plain').appendChild(instruction);

  /**
   * Gather available menu locations
   * Trigger check on locations changes
   */
  const locationsInputs = document.querySelectorAll('input[name^="menu-locations["]');
  locationsInputs.forEach(input => {
    input.addEventListener('change', () => {
      toggleMenuRules();
    });
  });
  const locations = Array.from(locationsInputs).map(input => {
    return input.getAttribute('name').match(/menu-locations\[(.*)\]/)[1];
  });

  /**
   * Check based on mutation observer, to detect
   * title changes, adding, removing, changing level and reordering of items
   */
  const observer = new MutationObserver(() => checkNavMenu(getCurrentConf()));
  const observerOpts = {subtree: true, childList: true, attributes: false};
  const enableObserver = () => {
    observer.observe(menuContent, observerOpts);
  };
  const disableObserver = () => {
    observer.disconnect();
  };

  /**
   * Show a notice message to inform to editors to prevent
   * using long texts to menu items
   */
  const showInfoNoticeMessage = () => {
    const container = document.createElement('div');
    container.classList.add('notice', 'notice-info', 'custom-notice-info', 'inline');

    const infoText = __(
      'Avoid using more than 5 items in the navigation menu, and limit the number of items when dealing with long text labels.',
      'planet4-master-theme-backend'
    );
    container.innerHTML = `<p>${infoText}</p>
    <style>
      .notice.custom-notice-info {
        background: transparent;
        border-top: none;
        border-right: none;
        margin-top: 0;
        margin-left: -10px;
        width: calc(100% - 8px);
      }
    </style>`;

    menuEditorFooter.insertBefore(container, menuEditorFooter.querySelector('div'));
  };

  /**
   * Toggle rules linked to location selected
   * Reset if no config available
   */
  const toggleMenuRules = () => {
    const conf = getCurrentConf();
    if (!conf) {
      wpNavMenu.options.globalMaxDepth = 11;
      disableObserver();
      displayErrors([]);
      return;
    }

    const infoNotice = document.querySelector('.custom-notice-info');
    if (!infoNotice) {
      showInfoNoticeMessage();
    }

    // Define depth limit for editor
    /* global wpNavMenu */
    wpNavMenu.options.globalMaxDepth = conf.maxDepth;
    enableObserver();
    checkNavMenu(conf);
  };

  /**
   * Check restrictions
   * Show errors on menu editor interface
   * @param {Object} conf
   */
  const checkNavMenu = conf => {
    const errors = [];
    if (!conf) {
      displayErrors(errors);
      return;
    }

    const items = menuContent.querySelectorAll(
      'li.menu-item:not(.sortable-placeholder)'
    );

    // Pull back menu items to the limited depth
    items.forEach(item => {
      checkDepth(item, conf.maxDepth, errors);
    });

    // Check root level
    const rootItems = document.querySelectorAll('.menu-item-depth-0:not(.sortable-placeholder)');
    const rootConf = getDepthConf(conf, 0);
    if (rootItems.length > rootConf.maxItems) {
      markItemsOverflow(errors, rootItems, rootConf.maxItems, __('Main menu', 'planet4-master-theme-backend'));
    }

    // Check each item for submenus and titles too long
    items.forEach(item => {
      const depth = getItemDepth(item);
      const children = getItemChildren(item);
      const depthConf = getDepthConf(conf, depth);

      const itemDiv = item.querySelector('.menu-item-title');
      const title = itemDiv ? itemDiv.textContent : '';

      // Too many children for the given item
      const subLevelConf = getDepthConf(conf, depth + 1);
      if (children && children.length > subLevelConf.maxItems) {
        markItemsOverflow(
          errors,
          children,
          subLevelConf.maxItems,
          // translators: %s: sub menu title
          sprintf(__('Sub menu "%s"', 'planet4-master-theme-backend'), title)
        );
      }

      // Item title is too long
      const titleCharsCount = [...title].length; // Count chars instead of code units
      if (titleCharsCount > depthConf.maxChars) {
        errors.push({
          target: item.id,
          message: sprintf(
            // translators: 1: item label, 2: max char count, 3: current char count
            __(
              'This item label "%1$s" should not be longer than %2$s characters (currently %3$s).',
              'planet4-master-theme-backend'
            ),
            title, depthConf.maxChars, titleCharsCount
          ),
        });
      }
    });

    // Display error messages and mark items in error
    displayErrors(errors);
  };

  /**
   * Get first checked location
   *
   * @return {string} The current location slug
   */
  const getCurrentLocation = () => {
    return locations.find(location => {
      return document.querySelector(`input[name="menu-locations[${location}]"]`).checked ?
        location : null;
    }) || null;
  };

  /**
   * Gets the current conf.
   *
   * @return {Object|null} The current conf
   */
  const getCurrentConf = () => {
    const location = getCurrentLocation();
    const conf = p4_menu_config[location] || null;
    if (!conf) {
      return null;
    }

    return {...{maxItems: 5, maxChars: 18, maxDepth: 1}, ...conf};
  };

  /**
   * Get item children
   *
   * @param {HTMLElement} item
   * @return {HTMLElement[]|null} Children list of this item
   */
  const getItemChildren = item => {
    const depth = getItemDepth(item);
    const itemsAfter = menuContent.querySelectorAll(
      `#${item.id} ~ li.menu-item:not(.sortable-placeholder)`
    );

    const children = [];
    for (const child of [...itemsAfter]) {
      const childDepth = getItemDepth(child);
      if (childDepth > depth + 1) { // sub-item, skipping
        continue;
      }
      if (childDepth <= depth) { // reached new same level item
        break;
      }
      children.push(child);
    }

    return children;
  };

  /**
   * Get the depth of the item
   *
   * @param {HTMLElement} item The item
   * @return {number} Depth of the item in the menu
   */
  const getItemDepth = item => {
    const depthClass = [...item.classList]
      .find(c => c.startsWith('menu-item-depth-'));
    return parseInt(depthClass.substr(-1, 1));
  };

  /**
   * Get the depth configuration (maxItems, maxChars)
   * Defaults to global conf
   *
   * @param {Object} conf
   * @param {number} depth The depth
   * @return {Object} The depth conf
   */
  const getDepthConf = (conf, depth) => {
    return (conf.depthConf && conf.depthConf[depth]) ? conf.depthConf[depth] : conf;
  };

  /**
   * Check menu items' depth and show an error message if needed
   *
   * @param {HTMLElement} item     The menu item
   * @param {number}      maxDepth The maximum allowed depth
   * @param {Array}       errors   The errors to be displayed
   */
  const checkDepth = (item, maxDepth, errors) => {
    const depth = getItemDepth(item);
    if (depth > maxDepth) {
      const itemDiv = item.querySelector('.menu-item-title');
      const title = itemDiv ? itemDiv.textContent : '';
      errors.push({
        target: item.id,
        message: sprintf(
          // translators: 1: item title, 2: item depth
          __(
            'Menus should have a depth of maximum 1 (currently item "%1$s" is at depth %2$s and therefore will not be displayed).',
            'planet4-master-theme-backend'
          ),
          title, depth
        ),
      });
    }
  };

  /**
   * Mark errors for too many items in a menu/submenu
   *
   * @param {Array}    errors   Errors
   * @param {NodeList} items    Items
   * @param {number}   maxItems Max number of items
   * @param {string}   menuName Menu name for the message
   */
  const markItemsOverflow = (errors, items, maxItems, menuName) => {
    errors.push({message: sprintf(
      // translators: 1: menu name, 2: max item count, 3: current item count
      __(
        '%1$s should not contain more than %2$s items at this level (currently %3$s).',
        'planet4-master-theme-backend'
      ),
      menuName, maxItems, items.length
    )});
    items.forEach((item, index) => {
      if (index >= maxItems) {
        errors.push({target: item.id});
      }
    });
  };

  /**
   * Shows errors in interface
   *
   * @param {Array} errors
   */
  const displayErrors = errors => {
    // Reset error markers
    menuContent.querySelectorAll('.menu-error').forEach(item => {
      item.classList.remove('menu-error');
    });

    const errorsContainer = getErrorsContainer();
    if (errors.length <= 0) {
      errorsContainer.remove();
      return;
    }

    // Mark error targets with error class
    errors.filter(err => err.target).forEach(err => {
      const menuItem = document.getElementById(err.target);
      if (menuItem) {
        menuItem.classList.add('menu-error');
      }
    });

    // List of error messages
    const errorList = errors.filter(err => err.message)
      .map(err => err.message ? `<li>${err.message}</li>` : null);
    const containerTitle = __('Menu configuration issues:', 'planet4-master-theme-backend');
    errorsContainer.innerHTML = `<strong>${containerTitle}</strong>
    <ul>
      ${errorList.join('')}
    </ul>
    <style>
      .menu-error .menu-item-handle {
        outline: 3px solid #d63638;
        outline-offset: -3px;
      }
      .errors-list {
        border-left: 4px solid #d63638;
        margin-inline-start: -10px;
        padding: 10px 10px 1px 10px;
      }
      .errors-list ul {
        margin: 5px 0 0 0;
        padding: 0 5px;
      }
      .errors-list ~ .major-publishing-actions .menu-save {
        background: #dba617;
        border-color: #dba617;
      }
    </style>`;
  };

  /**
   * Get the errors container, create it if needed
   *
   * @return {HTMLElement} The errors container
   */
  const getErrorsContainer = () => {
    let errorsContainer = menuEditorFooter.querySelector('.errors-list');

    if (!errorsContainer) {
      const container = document.createElement('div');
      container.classList.add('errors-list', 'notice-error');
      menuEditorFooter.insertBefore(container, menuEditorFooter.querySelector('div'));
      errorsContainer = container;
    }

    return errorsContainer;
  };

  // First run
  toggleMenuRules();
};

document.addEventListener('DOMContentLoaded', () => {
  menuEditorRestrictions();
});
