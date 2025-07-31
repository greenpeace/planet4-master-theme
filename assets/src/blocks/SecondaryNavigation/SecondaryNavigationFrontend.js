import {useState, useEffect, useRef} from '@wordpress/element';
import {getHeadingsFromDom} from '../TableOfContents/getHeadingsFromDom';
import {initializeJustifyContentAdjustment} from './adjustNavWidth';

const {__} = wp.i18n;

/**
 * Makes the secondary navigation sticky when the page header scrolls past
 * a defined offset and there's enough space in the content container.
 *
 * Adds/removes the 'stuck' class on `.sn-wrapper` based on scroll position.
 */
const makeSecondaryNavigationStickyOnScroll = () => {
  const pageHeader = document.querySelector('.is-pattern-p4-page-header');
  const stickyElement = document.querySelector('.sn-wrapper');
  const container = document.querySelector('.page-content');

  if (!pageHeader || !stickyElement || !container) {return;}

  // Create spacer
  const spacer = document.createElement('div');
  spacer.style.display = 'none';
  stickyElement.parentNode.insertBefore(spacer, stickyElement.nextSibling);

  const getDynamicOffset = () => {
    const mobile = window.innerWidth <= 991;
    const hasAdminBar = !!document.getElementById('wpadminbar');
    const hasStuckOpen = document.body.classList.contains('stuck-open');

    if (mobile) {
      if (hasStuckOpen) {return 110;}
      if (hasAdminBar) {return 86;}
      return 60;
    }
    if (hasAdminBar) {return 52;}
    return 20;
  };

  const onScroll = () => {
    const offset = getDynamicOffset();
    const pageHeaderRect = pageHeader.getBoundingClientRect();
    const stickyRect = stickyElement.getBoundingClientRect();
    const footer = document.querySelector('footer');
    spacer.style.height = `${stickyElement.offsetHeight}px`;

    let stopPoint = Infinity;
    if (footer) {
      const footerTop = footer.getBoundingClientRect().top + window.scrollY;
      stopPoint = footerTop - stickyRect.height - offset;
    }

    if (pageHeaderRect.bottom <= offset && window.scrollY < stopPoint) {
      stickyElement.classList.add('stuck');
      stickyElement.style.position = 'fixed';
      stickyElement.style.top = '';
      spacer.style.display = 'block';
    } else if (pageHeaderRect.bottom <= offset && window.scrollY >= stopPoint) {
      stickyElement.classList.add('stuck');
      stickyElement.style.position = 'absolute';
      stickyElement.style.top = `${stopPoint}px`;
      spacer.style.display = 'block';
    } else {
      stickyElement.classList.remove('stuck');
      stickyElement.style.position = '';
      stickyElement.style.top = '';
      stickyElement.style.width = '';
      spacer.style.display = 'none';
    }
  };

  window.addEventListener('scroll', onScroll);
  window.addEventListener('resize', onScroll);
};

export const SecondaryNavigationFrontend = ({levels}) => {
  const [activeLink, setActiveLink] = useState('');
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [currentHeaderLink, setCurrentHeaderLink] = useState('');
  const [showLeftArrow, setShowLeftArrow] = useState(false);
  const [showRightArrow, setShowRightArrow] = useState(false);
  const [isMobile, setIsMobile] = useState(false);
  const isClicking = useRef(false);
  const isManualScroll = useRef(false);
  const hasLoaded = useRef(false);
  const navListRef = useRef(null);
  const headings = getHeadingsFromDom(levels);
  const dropdownRef = useRef(null);
  const toggleRef = useRef(null);

  useEffect(() => {
    makeSecondaryNavigationStickyOnScroll();
    initializeJustifyContentAdjustment();
  }, []);

  useEffect(() => {
    const checkMobile = () => setIsMobile(window.innerWidth <= 991);
    checkMobile();
    window.addEventListener('resize', checkMobile);
    return () => window.removeEventListener('resize', checkMobile);
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

        if (id !== activeLink) {
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
        }
      });
    };

    const observer = new IntersectionObserver(observerCallback, observerOptions);

    headings.forEach(({anchor}) => {
      const link = document.getElementById(anchor);
      if (link) {observer.observe(link);}
    });

    setTimeout(() => hasLoaded.current = true, 500);

    return () => {
      headings.forEach(({anchor}) => {
        const link = document.getElementById(anchor);
        if (link) {observer.unobserve(link);}
      });
    };
  }, [activeLink, headings]);

  useEffect(() => {
  // Exit early if not on a mobile screen
    if (!isMobile) {return;}

    const hash = window.location.hash?.replace('#', '');
    if (!hash) {return;}

    const matchedHeading = headings.find(h => h.anchor === hash);
    if (matchedHeading) {
      setActiveLink(matchedHeading.anchor);
      setCurrentHeaderLink(matchedHeading.content);
    }
  }, [isMobile, headings]);

  useEffect(() => {
    // Adds the left and right arrows for horizontal scroll when the elements exceed container width.
    const navEl = navListRef.current;
    if (!navEl) {return;}

    const updateArrows = () => {
      const {scrollLeft, scrollWidth, clientWidth} = navEl;
      setShowLeftArrow(scrollLeft > 0);
      setShowRightArrow((scrollLeft + clientWidth + 10) < scrollWidth);
    };

    updateArrows();
    navEl.addEventListener('scroll', updateArrows);
    window.addEventListener('resize', updateArrows);

    return () => {
      navEl.removeEventListener('scroll', updateArrows);
      window.removeEventListener('resize', updateArrows);
    };
  }, []);

  useEffect(() => {
    if (!isMobile) {return;}

    // Close dropdown if user clicks outside of nav when open
    const handleClickOutside = event => {
      if (
        isDropdownOpen &&
      dropdownRef.current &&
      toggleRef.current &&
      !dropdownRef.current.contains(event.target) &&
      !toggleRef.current.contains(event.target)
      ) {
        setIsDropdownOpen(false);
      }
    };

    document.addEventListener('click', handleClickOutside);

    return () => {
      document.removeEventListener('click', handleClickOutside);
    };
  }, [isDropdownOpen, isMobile]);

  useEffect(() => {
    if (!isMobile) {return;}

    const footer = document.querySelector('footer');
    if (!footer || !dropdownRef.current) {return;}

    const onScroll = () => {
      if (!isDropdownOpen) {return;}

      const dropdownRect = dropdownRef.current.getBoundingClientRect();
      const footerRect = footer.getBoundingClientRect();

      // Close slightly before footer touches dropdown
      if (footerRect.top <= dropdownRect.bottom - 10) {
        setIsDropdownOpen(false);
      }
    };

    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, [isDropdownOpen, isMobile]);


  const handleClick = (id, text) => {
    isClicking.current = true;
    setActiveLink(id);
    setCurrentHeaderLink(text);
    setIsDropdownOpen(false);

    setTimeout(() => isClicking.current = false, 500);
  };

  const handleDropdown = () => setIsDropdownOpen(prev => !prev);

  const scrollLeft = () => {
    isManualScroll.current = true;
    navListRef.current?.scrollBy({left: -150, behavior: 'smooth'});

    // Reset after short delay
    setTimeout(() => isManualScroll.current = false, 500);
  };

  const scrollRight = () => {
    isManualScroll.current = true;
    navListRef.current.scrollBy({left: 150, behavior: 'smooth'});

    setTimeout(() => isManualScroll.current = false, 500);
  };

  return (
    <>
      {headings.length > 0 && (
        <div className="sn-wrapper">
          <div
            className="secondary-nav-dropdown"
            onClick={handleDropdown}
            onKeyDown={e => {
              if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                handleDropdown();
              }
            }}
            role="button"
            aria-expanded={isDropdownOpen}
            aria-label={isDropdownOpen ?  __('Close secondary dropdown', 'planet4-master-theme-backend') : __('Open secondary dropdown', 'planet4-master-theme-backend')}
            tabIndex={isMobile ? 0 : -1}
            ref={toggleRef}
          >
            <p className="current-active-class">{currentHeaderLink}</p>
            <p className={`dropdown-btn ${isDropdownOpen ? 'active' : ''}`}></p>
          </div>
          <div
            className={`block secondary-navigation-block ${isDropdownOpen ? 'show' : ''}`}
            ref={dropdownRef}
          >
            <div className="secondary-navigation-menu container">
              {showLeftArrow && (
                <button className="nav-arrow left" onClick={scrollLeft}>
              &#8592;
                </button>
              )}
              <div className="secondary-nav-scroll-wrapper">
                <ul className="secondary-navigation-item" ref={navListRef} aria-hidden={isMobile && !isDropdownOpen}>
                  {headings.map(({anchor, content}) => (
                    <li key={anchor}>
                      <a
                        className={`secondary-navigation-link ${activeLink === anchor ? 'active': ''}`}
                        href={`#${anchor}`}
                        data-target={anchor}
                        onClick={() => handleClick(anchor, content)}
                        tabIndex={isMobile && !isDropdownOpen ? -1 : 0}
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
      )}
    </>
  );
};
