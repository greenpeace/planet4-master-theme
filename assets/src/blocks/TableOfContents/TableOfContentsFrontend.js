import {getTableOfContentsStyle} from './getTableOfContentsStyle';
import {TableOfContentsItems} from './TableOfContentsItems';
import {makeHierarchical} from './makeHierarchical';
import {getHeadingsFromDom} from '../../functions/getHeadingsFromDom';
import {observeTimelineHeadings} from '../../functions/timelineObserver';

const {useState, useEffect} = wp.element;

export const TableOfContentsFrontend = ({title, className, levels, submenu_style}) => {

  const [headings, setHeadings] = useState([]);

  useEffect(() => {
    const updateHeadings = () => {
      setHeadings(getHeadingsFromDom(levels));
    };

    updateHeadings();

    const observer = observeTimelineHeadings(updateHeadings);

    return () => {
      observer?.();
    };
  }, [levels]);

  const menuItems = makeHierarchical(headings);
  const style = getTableOfContentsStyle(className, submenu_style);

  return (
    <section className={`block table-of-contents-block table-of-contents-${style} ${className ?? ''}`}>
      {!!title && <h2>{title}</h2>}
      <TableOfContentsItems menuItems={menuItems} />
    </section>
  );
};
