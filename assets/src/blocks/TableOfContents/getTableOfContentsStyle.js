import {getStyleFromClassName} from '../../functions/getStyleFromClassName';

// Map for old attribute 'submenu_style'
const TABLE_OF_CONTENTS_STYLES = {
  1: 'long',
  2: 'short',
  3: 'sidebar',
};

export const getTableOfContentsStyle = (className, submenu_style) => {
  const styleClass = getStyleFromClassName(className);
  if (styleClass) {
    return styleClass;
  }

  return submenu_style ? TABLE_OF_CONTENTS_STYLES[submenu_style] : 'long';
};
