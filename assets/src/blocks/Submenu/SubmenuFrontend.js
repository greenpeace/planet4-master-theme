import { Component, Fragment } from '@wordpress/element';
import { getSubmenuStyle, addSubmenuActions, loadMenuItems } from './submenuFunctions';
const { __ } = wp.i18n;

export class SubmenuFrontend extends Component {
  constructor(props) {
    super(props);
    this.state = {
      menuItems: []
    }

    this.loadMenuItems = this.loadMenuItems.bind(this);
  };

  componentDidMount() {
    // We need to add a small timeout for the editor,
    // otherwise the RichText elements appear empty
    setTimeout(this.loadMenuItems, 100);
  }

  componentDidUpdate({ levels: prevLevels }) {
    const { levels } = this.props;
    if (JSON.stringify(levels) !== JSON.stringify(prevLevels)) {
      this.loadMenuItems();
    }
  }

  loadMenuItems() {
    const { levels, isEditing } = this.props;
    const menuItems = loadMenuItems(levels, isEditing);
    if (menuItems && menuItems.length > 0) {
      this.setState({ menuItems }, () => {
        // This takes care of adding the "back to top" button,
        // and also the submenu links behaviour if needed
        if (!isEditing) {
          addSubmenuActions(menuItems);
        }
      });
    } else {
      this.setState({ menuItems: [] });
    }
  }

  onSubmenuLinkClick(id, hash) {
    const target = document.querySelectorAll(`[data-hash-target='${hash}']`)[0];
    if (target) {
      document.body.animate({
        scrollTop: target.offsetTop - 100
      }, 2000, () => {
        const position = window.pageYOffset;
        window.location.hash = `#${id}`;
        window.scrollTop(position);
      });
    }
  }

  getMenuItems(items) {
    return items.map(({ text, style, link, id, children, hash }) => (
      <li key={text} className={`list-style-${style || 'none'} ${link ? "list-link" : "list-heading"}`}>
        {link ?
          <a
            href={`#${id}`}
            className="icon-link submenu-link"
            data-hash={hash}
            onClick={() => this.onSubmenuLinkClick(id, hash)}
          >
            {text}
          </a>
          :
          <span className="submenu-heading">{text}</span>
        }
        {children && children.length > 0 &&
          <ul>
            {this.getMenuItems(children)}
          </ul>
        }
      </li>
    ));
  }

  render() {
    const { title, className, isEditing, submenu_style } = this.props;
    const { menuItems } = this.state;

    const style = getSubmenuStyle(className, submenu_style);

    return (
      <Fragment>
        {(isEditing || menuItems.length > 0) && (
          <section className={isEditing ? '' : `block submenu-block submenu-${style}`}>
            {title && !isEditing &&
              <h2>{title}</h2>
            }
            {menuItems.length > 0 &&
              <div className="submenu-menu">
                <ul className="submenu-item">
                  {this.getMenuItems(menuItems)}
                </ul>
              </div>
            }
            {isEditing && menuItems.length === 0 &&
              <div className='EmptyMessage'>{__('The submenu block produces no output on the editor.', 'planet4-blocks-backend')}</div>
            }
            {!isEditing && <a href="#" className="back-top">&nbsp;</a>}
          </section>
        )}
      </Fragment>
    );
  }
}
