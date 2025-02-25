import {useState, useEffect, useRef} from '@wordpress/element';
import {getHeadingsFromDom} from '../TableOfContents/getHeadingsFromDom';
import {initializeJustifyContentAdjustment} from './adjustNavWidth';

const isMobile = window.innerWidth <= 768;

const makeSecondaryNavigationStickyonScroll = () => {
  const pageHeader = document.querySelector('.is-pattern-p4-page-header');

  const stickyElement = document.querySelector('.sn-wrapper');
  const container = document.querySelector('.page-content');
  const offset = 20;

  window.addEventListener('scroll', () => {
    const containerRect = container.getBoundingClientRect();
    const stickyRect = stickyElement.getBoundingClientRect();
    const pageHeaderRect = pageHeader.getBoundingClientRect();

    if (pageHeaderRect.bottom <= offset && containerRect.bottom > stickyRect.height + offset) {
      stickyElement.classList.add('stuck');
    } else {
      stickyElement.classList.remove('stuck');
    }
  });
};

export const SecondaryNavigationFrontend = ({levels}) => {
  const [activeLink, setActiveLink] = useState('');
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [currentHeaderLink, setCurrentHeaderLink] = useState('');
  const isClicking = useRef(false);
  const hasLoaded = useRef(false);


  const headings = getHeadingsFromDom(levels);

  useEffect(() => {
    makeSecondaryNavigationStickyonScroll();
    initializeJustifyContentAdjustment();
  }, []);

  useEffect(() => {
    const observerOptions = {
      root: null,
      rootMargin: '0px',
      threshold: 1.0,
    };

    const observerCallback = entries => {
      if (isClicking.current) {return;}

      entries.forEach(entry => {
        if (entry.isIntersecting) {
          if (hasLoaded.current) {
            setActiveLink(entry.target.id);
            window.history.replaceState(null, null, `#${entry.target.id}`);
          }
        }
      });
    };

    const observer = new IntersectionObserver(observerCallback, observerOptions);

    headings.forEach(({anchor}) => {
      const link = document.getElementById(anchor);
      if (link) {observer.observe(link);}
    });

    setTimeout(() => {
      hasLoaded.current = true;
    }, 500);

    return () => {
      headings.forEach(({anchor}) => {
        const link = document.getElementById(anchor);
        if (link) {observer.unobserve(link);}
      });
    };
  }, []);

  useEffect(() => {
    if (isMobile) {
      const links = document.querySelectorAll('.secondary-navigation-item a');
      links.forEach(item => item.classList.remove('active')); // remove from all
      if (links[0]) {
        links[0].classList.add('active'); // add to first only
      }
    }
  }, []);

  useEffect(() => {
    if (isMobile) {
      const updateActiveLink = () => {
        const active = document.querySelector('.secondary-navigation-link.active');
        const mobileNav = document.querySelector('.mobile-menu-hidden');
        const stickyNav = document.querySelector('.stuck');

        if(!mobileNav) {
          stickyNav.style.top = '110px';
        } else {
          stickyNav.style.top = '60px';
        }

        if (active) {
          setCurrentHeaderLink(active.textContent);
        }
      };

      window.addEventListener('scroll', updateActiveLink);
      return () => window.removeEventListener('scroll', updateActiveLink);
    }
  }, []);


  const handleClick = (id, text) => {
    isClicking.current = true;
    setActiveLink(id);
    setCurrentHeaderLink(text);
    setIsDropdownOpen(false);

    setTimeout(() => {
      isClicking.current = false;
    }, 500);
  };

  const handleDropdown = () => {
    setIsDropdownOpen(prev => !prev);
  };

  return (
    <div className="sn-wrapper">
      <div className="secondary-nav-dropdown">
        <p className="current-active-class">{currentHeaderLink}</p>
        <p
          className={`dropdown-btn ${isDropdownOpen ? 'active' : ''}`}
          onClick={handleDropdown}
          role="presentation"
        >
        </p>
      </div>
      <div className={`block secondary-navigation-block ${isDropdownOpen ? 'show' : ''}`}>
        <div className="secondary-navigation-menu container">
          <ul className="secondary-navigation-item">
            {headings.map(({anchor, content}) => (
              <li
                key={anchor}
              >
                <a
                  className={`secondary-navigation-link ${activeLink === anchor ? 'active': ''}`}
                  href={`#${anchor}`}
                  data-target={anchor}
                  onClick={() => handleClick(anchor, content)}
                >
                  {content}
                </a>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  );
};
