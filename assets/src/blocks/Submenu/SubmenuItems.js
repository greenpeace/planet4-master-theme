export const SubmenuItems = ({ menuItems }) => {

  const onSubmenuLinkClick = id => {
    const target = document.getElementById(id);
    if (target) {
      $('html, body').animate({
        scrollTop: target.offsetTop - 100
      }, 2000, () => {
        const position = window.pageYOffset;
        window.location.hash = id;
        window.scrollTo(0, position);
      });
    }
  }

  const renderMenuItems = (items) => {
    return items.map(({ text, style, link, id, children }) => (
      <li key={text} className={`list-style-${style || 'none'} ${link ? "list-link" : "list-heading"}`}>
        {link ?
          <a
            href={`#${id}`}
            className="icon-link submenu-link"
            onClick={event => {
              event.preventDefault();
              onSubmenuLinkClick(id);
            }}
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
