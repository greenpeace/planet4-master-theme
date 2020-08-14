export const makeHierarchical = headings => {
  let previousMenuItem;

  return headings.reduce((menuItems, heading) => {
    const { level, shouldLink, anchor, content, style } = heading;

    // const parent = deeperThanPrevious ? previousHeading.children : menuItems;
    let possibleParent = previousMenuItem || menuItems;

    while (possibleParent.level && possibleParent.level >= level) {
      possibleParent = possibleParent.parent;
    }

    const parent = possibleParent;

    const container = parent === menuItems ? menuItems : parent.children;

    const menuItem = {
      text: content,
      style: style,
      children: [],
      parent: parent,
      level,
      shouldLink,
      anchor,
    };
    container.push(menuItem);

    previousMenuItem = menuItem;

    return menuItems;
  }, []);
};
