import {useState, useEffect, useRef} from '@wordpress/element';
import {getHeadingsFromDom} from '../../functions/getHeadingsFromDom';

const {__} = wp.i18n;

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
  const visibleRef = useRef([]);
  const isRTL = document.dir === 'rtl';
  const pageHeader = document.querySelector('.is-pattern-p4-page-header');

  useEffect(() => {
    const block = document.querySelector('div[data-render="planet4-blocks/secondary-navigation"]');

    if (!pageHeader || !block) {return;}

    document.body.classList.add('secondary-nav-class');
    document.body.classList.add('overflow-visible');
  }, [pageHeader]);

  // Check runs to confirm if we are on mobile
  useEffect(() => {
    const checkMobile = () => setIsMobile(window.innerWidth <= 991);
    checkMobile();
    window.addEventListener('resize', checkMobile);
    return () => window.removeEventListener('resize', checkMobile);
  }, []);

  // This runs to auto update the URL and active header class for the Secondary Navigation
  // Works on scroll
  useEffect(() => {
    if (!pageHeader) {return;}
    const observerOptions = {
      root: null,
      rootMargin: '0px',
      threshold: 1.0,
    };

    const observerCallback = entries => {
      if (isClicking.current || isManualScroll.current || !hasLoaded.current) {return;}

      // Update ref based on visible headings/entries
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          if (!visibleRef.current.includes(entry.target)) {
            visibleRef.current.push(entry.target);
          }
        } else {
          visibleRef.current = visibleRef.current.filter(el => el !== entry.target);
        }
      });

      // Using this so we update just before the next 'repaint'
      // This prevents flicker between header elements
      requestAnimationFrame(() => {
        if (visibleRef.current.length === 0) {return;}

        // Pick the closest visible header to the top
        const currentHeaderElement = visibleRef.current.reduce((closest, el) => {
          const rect = el.getBoundingClientRect();
          if (!closest) {return {el, top: rect.top};}
          return rect.top < closest.top ? {el, top: rect.top} : closest;
        }, null);

        if (currentHeaderElement && currentHeaderElement.el.id !== activeLink) {
          setActiveLink(currentHeaderElement.el.id);
          setCurrentHeaderLink(currentHeaderElement.el.innerText);
          window.history.replaceState(null, null, `#${currentHeaderElement.el.id}`);

          // Make active nav element visible
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

    return () => observer.disconnect();
  }, [activeLink, headings, pageHeader]);

  // On mobile we update the navbar with the current active element
  // Does not run if there isn't one
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

  // This adds arrows to the container to indicate more elements available
  // It also works for RTL sites
  useEffect(() => {
    const navEl = navListRef.current;
    if (!navEl) {return;}

    const items = navEl.querySelectorAll('li');
    if (!items.length) {return;}

    const firstItem = items[0];
    const lastItem = items[items.length - 1];

    const observer = new IntersectionObserver(
      entries => {
        for (const entry of entries) {
          if (entry.target === firstItem) {
            if (isRTL) {
              setShowRightArrow(!entry.isIntersecting);
            } else {
              setShowLeftArrow(!entry.isIntersecting);
            }
          }
          if (entry.target === lastItem) {
            if (isRTL) {
              setShowLeftArrow(!entry.isIntersecting);
            } else {
              setShowRightArrow(!entry.isIntersecting);
            }
          }
        }
      },
      {root: navEl, threshold: 1.0}
    );

    observer.observe(firstItem);
    observer.observe(lastItem);

    return () => observer.disconnect();
  }, [isRTL]);

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

    return () => document.removeEventListener('click', handleClickOutside);
  }, [isDropdownOpen, isMobile]);

  // This is is to ensure if the user scrolls further down on mobile to where the
  // Footer intercepts the dropdown, it closes the dropdown automatically
  useEffect(() => {
    if (!isMobile || !dropdownRef.current) {return;}

    const footer = document.querySelector('footer');
    if (!footer) {return;}

    const observerCallback = entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting && isDropdownOpen) {
          setIsDropdownOpen(false);
        }
      });
    };

    const observer = new IntersectionObserver(observerCallback, {
      root: null,
      threshold: 0,
    });

    observer.observe(footer);

    return () => {
      observer.disconnect();
    };

  }, [isDropdownOpen, isMobile]);


  const handleClick = (id, text) => {
    const isFirefox = navigator.userAgent.toLowerCase().includes('firefox');

    if (isFirefox) {
      const target = document.getElementById(id);
      if (target) {
        target.scrollIntoView({behavior: 'smooth', block: 'start'});
      }
      //Ensures dropdown is updated with current header title
      window.history.replaceState(null, null, `#${id}`);
    }

    isClicking.current = true;
    setActiveLink(id);
    setCurrentHeaderLink(text);
    setIsDropdownOpen(false);


    setTimeout(() => isClicking.current = false, 500);
  };

  const handleDropdown = () => setIsDropdownOpen(prev => !prev);

  const scrollNav = direction => {
    isManualScroll.current = true;

    const distance = 150;

    const scrollBy = direction === 'right' ? distance : -distance;

    navListRef.current?.scrollBy({left: scrollBy, behavior: 'smooth'});

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
            aria-label={isDropdownOpen ?  __('Close secondary navigation dropdown menu', 'planet4-master-theme-backend') : __('Open secondary navigation dropdown menu', 'planet4-master-theme-backend')}
            tabIndex={isMobile ? 0 : -1}
            ref={toggleRef}
          >
            <p className="current-active-class">{currentHeaderLink}</p>
            <p className={`dropdown-btn ${isDropdownOpen ? 'active' : ''}`}/>
          </div>
          <div
            className={`block secondary-navigation-block ${isDropdownOpen ? 'show' : ''}`}
            ref={dropdownRef}
          >
            <div className="secondary-navigation-menu container">
              {showLeftArrow && (
                <button className="nav-arrow left" onClick={() => scrollNav('left')}/>
              )}
              <div className="secondary-nav-scroll-wrapper">
                <ul className={`secondary-navigation-items ${isMobile || showRightArrow || showLeftArrow ? 'justify-content-start' : 'justify-content-center'}`}
                  ref={navListRef}
                  aria-hidden={isMobile && !isDropdownOpen}
                >
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
                <button className="nav-arrow right" onClick={() => scrollNav('right')}/>
              )}
            </div>
          </div>
        </div>
      )}
    </>
  );
};
