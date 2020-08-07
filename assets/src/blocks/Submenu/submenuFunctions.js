// Map for old attribute 'submenu_style'
const SUBMENU_STYLES = {
  1: 'long',
  2: 'short',
  3: 'sidebar'
};

export const getSubmenuStyle = (className, submenu_style) => {
  if (className && className.includes('is-style-')) {
    return className.split('is-style-')[1];
  }
  return submenu_style ? SUBMENU_STYLES[submenu_style] : 'long';
};

export const addSubmenuActions = submenu => {
  if (submenu && Array.isArray(submenu)) {
    for (let i = 0; i < submenu.length; i++) {
      const menu = submenu[i];
      addTargetIds(menu);
      formatChildren(menu);
    }

    // Add "back to top" button behaviour
    let backtop = document.querySelector('a.back-top');
    const submenuBlock = document.querySelector('section.submenu-block');

    if (submenuBlock) {
      // If back to top button doesn't exist yet, we need to create it
      if (!backtop) {
        backtop = document.createElement('a');
        backtop.href = '#';
        backtop.className = 'back-top';
        document.body.appendChild(backtop);
      }
      addBackToTopBehaviour(backtop);
    }
  }
};

// Add onscroll function and proper positioning for back to top behaviour
const addBackToTopBehaviour = backtop => {
  const cookies = document.getElementById('set-cookie');
  window.onscroll = () => {
    if (window.pageYOffset > 400 && backtop.style.display !== 'block') {
      backtop.style.display = 'block';
      if (cookies) {
        const cookiesStyles = window.getComputedStyle(cookies);
        if (cookiesStyles && cookiesStyles.display !== 'none') {
          backtop.style.bottom = '120px';
        } else {
          backtop.style.bottom = '50px';
        }
      }
    } else if (window.pageYOffset <= 400 && backtop.style.display !== 'none') {
      backtop.style.display = 'none';
    }
  };
};

/**
 * Format submenu entry children.
 *
 * @param menu Submenu entry
 */
const formatChildren = menu => {
  if (menu.children && Array.isArray(menu.children)) {
    for (let k = 0; k < menu.children.length; k++) {
      const child = menu.children[k];
      addTargetIds(child);
      formatChildren(child);
    }
  }
};

/**
 * Add ids to the items, to be able to scroll to them.
 *
 * @param item Submenu menu item
 */
const addTargetIds = item => {
  if (item.link) {
    const headings = getHeadings(item.type);
    if (headings) {
      headings.forEach(heading => {
        if (heading.textContent === item.text && !heading.id) {
          heading.id = item.id;
        }
      });
    }
  }
};

export const getHeadings = (headings, className = 'page-template') => {
  // We need to make sure it's a div element,
  // since for 'page-template' className we have it on the body too
  const page = document.querySelector(`div.${className}`);
  return page ? [...page.querySelectorAll(headings)] : null;
};
