import {getStyleFromClassName} from '../../functions/getStyleFromClassName';

// Map for old attribute 'submenu_style'
const SUBMENU_STYLES = {
  1: 'long',
  2: 'short',
  3: 'sidebar',
};

export const getSubmenuStyle = (className, submenu_style) => {
  const styleClass = getStyleFromClassName(className);
  if (styleClass) {
    return styleClass;
  }

  return submenu_style ? SUBMENU_STYLES[submenu_style] : 'long';
};
