// Map for old attribute 'submenu_style'
const SUBMENU_STYLES = {
  1: 'long',
  2: 'short',
  3: 'sidebar'
};

export const getSubmenuStyle = (className, submenu_style) => {
  let style = submenu_style ? SUBMENU_STYLES[submenu_style] : 'long';
  if (className && className.includes('is-style-')) {
    style = className.split('is-style-')[1];
  }
  return style;
};

export const addSubmenuActions = submenu => {
  if (submenu && Array.isArray(submenu)) {
    for (let i = 0; i < submenu.length; i++) {
      const menu = submenu[i];
      addTargetLinks(menu);
      addChildrenLinks(menu);
    }

    // Add "back to top" button behavior
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
function addChildrenLinks(menu) {
  if (menu.children && Array.isArray(menu.children)) {
    for (let k = 0; k < menu.children.length; k++) {
      const child = menu.children[k];
      addTargetLinks(child);
      addChildrenLinks(child);
    }
  }
}

/**
 * Append html links the given item.
 *
 * @param item Submenu menu item
 */
function addTargetLinks(item) {
  if (item.link) {
    const headings = [...document.getElementsByTagName(item.type)];
    for (let l = 0; l < headings.length; l++) {
      const heading = headings[l];
      if (heading.innerText.replace(/\u2010|\u2011|\u2013/, '') === item.text.replace('-', '')) {
        let targetLink = document.createElement('a');
        targetLink.id = item.id;
        targetLink.setAttribute('data-hash-target', item.hash);
        heading.appendChild(targetLink);
      }
    }
  }
}
