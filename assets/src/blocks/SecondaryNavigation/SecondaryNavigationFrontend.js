import {getHeadingsFromDom} from '../TableOfContents/getHeadingsFromDom';

export const SecondaryNavigationFrontend = ({levels}) => {
  const headings = getHeadingsFromDom(levels);

  return (
    <section className="block secondary-navigation-block">
      <div className="secondary-navigation-menu">
        <ul className="secondary-navigation-item">
          {headings.map(({anchor, text, shouldLink}) => (
            <li key={anchor} className={`list-style-none ${shouldLink ? 'list-link' : 'list-heading'}`}>
              {shouldLink ?
                <a
                  className="icon-link secondary-navigation-link"
                  href={`#${anchor}`}
                >
                  {text}
                </a> :
                <span className="secondary-navigation-heading">{text}</span>
              }
            </li>
          ))}
        </ul>
      </div>
    </section>
  );
};
