import {getTableOfContentsStyle} from './getTableOfContentsStyle';
import {TableOfContentsItems} from './TableOfContentsItems';
import {makeHierarchical} from './makeHierarchical';
import {getHeadingsFromDom} from './getHeadingsFromDom';

export const TableOfContentsFrontend = ({title, className, levels, table_of_contents_style}) => {
  const headings = getHeadingsFromDom(levels);
  const menuItems = makeHierarchical(headings);
  const style = getTableOfContentsStyle(className, table_of_contents_style);

  return (
    <section className={`block table-of-contents-block table-of-contents-${style} ${className ?? ''}`}>
      {!!title && (
        <h2>{ title }</h2>
      )}
      <TableOfContentsItems menuItems={menuItems} />
    </section>
  );
};
