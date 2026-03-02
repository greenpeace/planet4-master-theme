import {useState, useEffect, useRef} from '@wordpress/element';

export const YearsNavigation = ({years}) => {
  const [showLeftArrow, setShowLeftArrow] = useState(false);
  const [showRightArrow, setShowRightArrow] = useState(false);
  const [activeYear, setActiveYear] = useState('');
  const yearsListRef = useRef(null);
  const isManualScroll = useRef(false);
  const isClicking = useRef(false);
  const visibleRef = useRef([]);

  const isRTL = document.dir === 'rtl';

  const handleClick = year => {
    const isFirefox = navigator.userAgent.toLowerCase().includes('firefox');

    if (isFirefox) {
      const target = document.getElementById(year);
      if (target) {
        target.scrollIntoView({behavior: 'smooth', block: 'start'});
      }
      // Ensures dropdown is updated with current year
      window.history.replaceState(null, null, `#${year}`);
    }

    isClicking.current = true;
    setActiveYear(year);

    setTimeout(() => isClicking.current = false, 500);
  };

  const scrollNav = direction => {
    isManualScroll.current = true;

    const distance = 150;

    const scrollBy = direction === 'right' ? distance : -distance;

    yearsListRef.current?.scrollBy({left: scrollBy, behavior: 'smooth'});

    setTimeout(() => isManualScroll.current = false, 500);
  };

  // This runs to auto update the URL and active year class, works on scroll
  useEffect(() => {
    const observerOptions = {
      root: null,
      rootMargin: '0px',
      threshold: 1.0,
    };

    const observerCallback = entries => {
      if (isClicking.current || isManualScroll.current) {return;}

      // Update ref based on visible years/entries
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
      // This prevents flicker between year elements
      requestAnimationFrame(() => {
        if (visibleRef.current.length === 0) {return;}

        // Pick the closest visible year to the top
        const currentHeaderElement = visibleRef.current.reduce((closest, el) => {
          const rect = el.getBoundingClientRect();
          if (!closest) {return {el, top: rect.top};}
          return rect.top < closest.top ? {el, top: rect.top} : closest;
        }, null);

        if (currentHeaderElement && currentHeaderElement.el.id !== activeYear) {
          setActiveYear(currentHeaderElement.el.id);
          window.history.replaceState(null, null, `#${currentHeaderElement.el.id}`);

          // Make active year element visible
          setTimeout(() => {
            const navItem = document.querySelector('.years-navigation-items a.active');
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

    years.forEach(year => {
      const link = document.getElementById(year);
      if (link) {observer.observe(link);}
    });

    return () => observer.disconnect();
  }, [activeYear, years]);

  // Make sure that the body has overflow set to "visible", otherwise the "sticky"
  // position of the navigation won't work.
  useEffect(() => document.body.classList.add('overflow-visible'), []);

  // This adds arrows to the container to indicate more elements available
  // It also works for RTL sites
  useEffect(() => {
    const navEl = yearsListRef.current;
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

  return (
    <div className="years-navigation d-flex">
      {showLeftArrow && (
        <button className="nav-arrow left" onClick={() => scrollNav('left')}/>
      )}
      <ul className="years-navigation-items d-flex" ref={yearsListRef}>
        {years.map(year => (
          <li key={year}>
            <a
              href={`#${year}`}
              onClick={() => handleClick(year)}
              data-target={year}
              className={`${activeYear === year ? 'active': ''}`}
            >
              {year}
            </a>
          </li>
        ))}
      </ul>
      {showRightArrow && (
        <button className="nav-arrow right" onClick={() => scrollNav('right')}/>
      )}
    </div>
  );
};
