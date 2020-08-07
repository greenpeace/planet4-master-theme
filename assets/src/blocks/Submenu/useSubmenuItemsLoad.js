import { useState, useEffect } from '@wordpress/element';
import { getHeadings } from './submenuFunctions';

export const useSubmenuItemsLoad = (levels, isEditing) => {

  const [menuItems, setMenuItems] = useState([]);

  const getHeadingNumber = tag => Number(tag.tagName.replace('H', ''));

  const loadMenuItems = () => {
    // Get all heading tags that we need to query
    const headings = levels.map(level => `h${level.heading}`);
    const tags = getHeadings(headings, isEditing ? 'editor-styles-wrapper' : 'page-template');
    if (!tags) {
      return [];
    }
    return tags.reduce((menuItems, tag, index) => {
      const headingNumber = getHeadingNumber(tag);
      let previousHeadingNumber = 0;
      if (index > 0) {
        previousHeadingNumber = getHeadingNumber(tags[index - 1]);
      }
      // Get the properties that we need to create the new menu item
      const correspondingLevel = levels.find(level => level.heading === headingNumber);
      const id = tag.id || tag.textContent.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, ''); // equivalent of WP sanitize_title function
      const menuItem = {
        text: tag.textContent,
        id: `${id}-h${headingNumber}-${index}`,
        style: correspondingLevel.style,
        link: correspondingLevel.link,
        type: `h${headingNumber}`,
        children: []
      };
      if (previousHeadingNumber && headingNumber > previousHeadingNumber) {
        // In this case we need to add this menuItem to the children of the previous one
        menuItems[menuItems.length - 1].children.push(menuItem);
      } else {
        menuItems.push(menuItem);
      }
      return menuItems;
    }, []);
  };

  useEffect(() => {
    const items = loadMenuItems();
    setMenuItems(items);
  }, [levels]);

  return { menuItems };
};
