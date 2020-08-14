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
