export const TableOfContentsItems = ({menuItems}) => {
  const renderMenuItems = items => {
    return items.map(({anchor, text, style, shouldLink, children}) => (
      <li key={anchor} className={`list-style-${style || 'none'} ${shouldLink ? 'list-link' : 'list-heading'}`}>
        {shouldLink ?
          <a
            className="icon-link table-of-contents-link"
            href={`#${anchor}`}
          >
            {text}
          </a> :
          <span className="table-of-contents-heading">{text}</span>
        }
        {children && children.length > 0 &&
          <ul>
            {renderMenuItems(children)}
          </ul>
        }
      </li>
    ));
  };

  return menuItems.length > 0 && (
    <div className="table-of-contents-menu">
      <ul className="table-of-contents-item">
        {renderMenuItems(menuItems)}
      </ul>
    </div>
  );
};
