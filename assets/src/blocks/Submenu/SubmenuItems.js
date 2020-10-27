export const SubmenuItems = ({ menuItems }) => {

  const renderMenuItems = (items) => {
    return items.map(({ anchor, text, style, shouldLink, children }) => (
      <li key={anchor} className={`list-style-${style || 'none'} ${shouldLink ? "list-link" : "list-heading"}`}>
        {shouldLink ?
          <a
            className="icon-link submenu-link"
            href={`#${anchor}`}
          >
            {text}
          </a>
          :
          <span className="submenu-heading">{text}</span>
        }
        {children && children.length > 0 &&
          <ul>
            {renderMenuItems(children)}
          </ul>
        }
      </li>
    ));
  }

  return menuItems.length > 0 && (
    <div className="submenu-menu">
      <ul className="submenu-item">
        {renderMenuItems(menuItems)}
      </ul>
    </div>
  );
}
