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
      addTargetLinks(menu);
      addChildrenLinks(menu);
    }

    // Add "back to top" button behaviour
    const backtop = document.getElementsByClassName('back-top')[0];
    const submenuBlock = document.getElementsByClassName('submenu-block')[0];
    const cookiesBlock = document.getElementById('set-cookie');

    if (submenuBlock) {
      window.onscroll = () => {
        if (window.pageYOffset > 400) {
          backtop.style.display = 'block';
          if (cookiesBlock && cookiesBlock.style.display !== 'none') {
            backtop.style.bottom = '120px';
          } else {
            backtop.style.bottom = '50px';
          }
        } else {
          backtop.style.display = 'none';
        }
      };
    }
  }
};

/**
 * Append html links for a submenu entry children.
 *
 * @param menu Submenu entry
 */
const addChildrenLinks = menu => {
  if (menu.children && Array.isArray(menu.children)) {
    for (let k = 0; k < menu.children.length; k++) {
      const child = menu.children[k];
      addTargetLinks(child);
      addChildrenLinks(child);
    }
  }
};

/**
 * Append html links the given item.
 *
 * @param item Submenu menu item
 */
const addTargetLinks = item => {
  if (item.link) {
    const headings = getTags(item.type);
    if (headings) {
      headings.forEach(heading => {
        if (heading.textContent === item.text) {
          let targetLink = document.createElement('a');
          targetLink.id = item.id;
          targetLink.setAttribute('data-hash-target', item.hash);
          heading.appendChild(targetLink);
        }
      });
    }
  }
};

export const loadMenuItems = (levels, isEditing) => {
  const menuItems = [];
  // Get all heading tags that we need to query
  const headings = levels.map(level => `h${level.heading}`);
  const tagElements = getTags(headings, isEditing ? 'editor-styles-wrapper' : 'page-template');
  if (tagElements) {
    tagElements.forEach((tagElement, tagIndex) => {
      const headingNumber = getTagElementHeadingNumber(tagElement);
      let previousHeadingNumber = 0;
      if (tagIndex > 0) {
        previousHeadingNumber = getTagElementHeadingNumber(tagElements[tagIndex - 1]);
      }
      // Get the properties that we need to create the new menu item
      const correspondingLevel = levels.find(level => level.heading === headingNumber);
      const id = tagElement.textContent.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, ''); // equivalent of WP sanitize_title function
      const menuItem = {
        text: tagElement.textContent,
        id,
        style: correspondingLevel.style,
        link: correspondingLevel.link,
        type: `h${correspondingLevel.heading}`,
        hash: `${id}-h${correspondingLevel.heading}-${tagIndex}`,
        children: []
      };
      if (previousHeadingNumber && headingNumber > previousHeadingNumber) {
        // In this case we need to add this menuItem to the children of the previous one
        menuItems[menuItems.length - 1].children.push(menuItem);
      } else {
        menuItems.push(menuItem);
      }
    });
  }
  return menuItems;
};

const getTags = (headings, className = 'page-template') => {
  // We need to make sure it's a div element,
  // since for 'page-template' className we have it on the body too
  const page = [...document.getElementsByClassName(className)].find(element => element.tagName === 'DIV');
  if (page) {
    return [...page.querySelectorAll(headings)];
  }
  return null;
};

const getTagElementHeadingNumber = tagElement => Number(tagElement.tagName.replace('H', ''));
