import {getStyleFromClassName} from '../../functions/getStyleFromClassName';

// Map for old attribute 'table_of_contents_style'
const TABLE_OF_CONTENTS_STYLES = {
  1: 'long',
  2: 'short',
  3: 'sidebar',
};

export const getTableOfContentsStyle = (className, table_of_contents_style) => {
  const styleClass = getStyleFromClassName(className);
  if (styleClass) {
    return styleClass;
  }

  return table_of_contents_style ? TABLE_OF_CONTENTS_STYLES[table_of_contents_style] : 'long';
};
