import {useState, useEffect, useRef} from '@wordpress/element';
import {getHeadingsFromDom} from '../TableOfContents/getHeadingsFromDom';
import {initializeJustifyContentAdjustment} from './adjustNavWidth';

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
  const [showLeftArrow, setShowLeftArrow] = useState(false);
  const [showRightArrow, setShowRightArrow] = useState(false);
  const isClicking = useRef(false);
  const isManualScroll = useRef(false);
  const hasLoaded = useRef(false);
  const navListRef = useRef(null);

  const isMobile = window.innerWidth <= 991;


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
      if (isClicking.current || isManualScroll.current) {return;}

      // Update the active class for the right heading element on scroll.
      entries.forEach(entry => {
        if (!entry.isIntersecting || !hasLoaded.current) {return;}

        const {id, innerText} = entry.target;

        setActiveLink(id);
        setCurrentHeaderLink(innerText);
        window.history.replaceState(null, null, `#${id}`);

        // Make active element visible on scroll
        setTimeout(() => {
          const navItem = document.querySelector('.secondary-navigation-link.active');
          navItem?.scrollIntoView({
            behavior: 'smooth',
            inline: 'center',
            block: 'nearest',
          });
        }, 0);
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
  }, [headings]);

  useEffect(() => {
    // For smaller screens to update the new Navigation with the active element.
    if (isMobile) {
      const hash = window.location.hash?.replace('#', '');

      if (hash) {
        const matchedHeading = headings.find(h => h.anchor === hash);
        if (matchedHeading) {
          setActiveLink(matchedHeading.anchor);
          setCurrentHeaderLink(matchedHeading.content);
        }
      }
    }
  }, [isMobile, headings]);

  useEffect(() => {
    // Adds the left and right arrows for horizontal scroll when the elements exceed container width.
    const navEl = navListRef.current;
    if (!navEl) {return;}

    const updateArrows = () => {
      const {scrollLeft, scrollWidth, clientWidth} = navEl;
      setShowLeftArrow(scrollLeft > 0);
      setShowRightArrow(scrollLeft + clientWidth < scrollWidth);
    };

    updateArrows();
    navEl.addEventListener('scroll', updateArrows);
    window.addEventListener('resize', updateArrows);

    return () => {
      navEl.removeEventListener('scroll', updateArrows);
      window.removeEventListener('resize', updateArrows);
    };
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

  const scrollLeft = () => {
    isManualScroll.current = true;
    navListRef.current?.scrollBy({left: -150, behavior: 'smooth'});

    // Reset after short delay
    setTimeout(() => {
      isManualScroll.current = false;
    }, 500);
  };

  const scrollRight = () => {
    isManualScroll.current = true;
    navListRef.current.scrollBy({left: 150, behavior: 'smooth'});

    setTimeout(() => {
      isManualScroll.current = false;
    }, 500);
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
          {showLeftArrow && (
            <button className="nav-arrow left" onClick={scrollLeft}>
              &#8592;
            </button>
          )}
          <div className="secondary-nav-scroll-wrapper">
            <ul className="secondary-navigation-item" ref={navListRef}>
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
          {showRightArrow && (
            <button className="nav-arrow right" onClick={scrollRight}>
              &#8594;
            </button>
          )}
        </div>
      </div>
    </div>
  );
};
