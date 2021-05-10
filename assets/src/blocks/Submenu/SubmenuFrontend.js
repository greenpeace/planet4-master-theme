import { getSubmenuStyle } from './getSubmenuStyle';
import { SubmenuItems } from './SubmenuItems';
import { makeHierarchical } from './makeHierarchical';
import { getHeadingsFromDom } from './getHeadingsFromDom';

export const SubmenuFrontend = ({ title, className, levels, submenu_style }) => {

  const headings = getHeadingsFromDom(levels);
  const menuItems = makeHierarchical(headings);
  const style = getSubmenuStyle(className, submenu_style);

  return (
    <section className={ `block submenu-block submenu-${ style }` }>
      { !!title && (
        <h2>{ title }</h2>
      ) }
      <SubmenuItems menuItems={ menuItems }/>
    </section>
  );
};
