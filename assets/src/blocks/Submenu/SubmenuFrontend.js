import { Fragment, useEffect } from '@wordpress/element';
import { getSubmenuStyle, addSubmenuActions } from './submenuFunctions';
import { SubmenuItems } from './SubmenuItems';
import { useSubmenuItemsLoad } from './useSubmenuItemsLoad';

export const SubmenuFrontend = ({ title, className, levels, submenu_style }) => {

  const { menuItems } = useSubmenuItemsLoad(levels, false);

  useEffect(() => addSubmenuActions(menuItems), [menuItems]);

  const style = getSubmenuStyle(className, submenu_style);

  return (
    <section className={`block submenu-block submenu-${style}`}>
      <h2>{title}</h2>
      <SubmenuItems menuItems={menuItems} />
    </section>
  );
}
