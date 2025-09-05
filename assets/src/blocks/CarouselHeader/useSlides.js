import Hammer from 'hammerjs';
const {useEffect, useState, useCallback, useRef} = wp.element;

const activeClass = 'active';

/**
 * Takes an array of refs to the slides
 * and performs the following transition:
 *
 * - Adds an *enter* transition class for the `next` element
 * - Adds an *exit* transition class for the `active` element
 * - Adds a listener for `ontransitionend` to the `active` element
 *
 * @param {Array}   slidesRef
 * @param {*}       totalSlides
 * @param {*}       containerRef
 * @param {boolean} carousel_autoplay
 * @param {Object}  options
 * @return {*} functions for the carousel header slides
 */
export const useSlides = (slidesRef, totalSlides, containerRef, carousel_autoplay, options = {
  // Following Bootstrap's approach for RTL:
  // https://getbootstrap.com/docs/5.0/getting-started/rtl/#approach
  // Note: in non-directional transitions (e.g.: fade out),
  // these could have the same class for both directions.
  enterTransitionClasses: {
    next: 'enter-from-end',
    prev: 'enter-from-start',
  },
  exitTransitionClasses: {
    next: 'exit-to-start',
    prev: 'exit-to-end',
  },
}) => {
  const [autoplay, setAutoplay] = useState(carousel_autoplay);
  const [currentSlide, setCurrentSlide] = useState(0);
  const [sliding, setSliding] = useState(false);
  // Set up the autoplay for the slides
  const timerRef = useRef(null);

  const isRTL = document.querySelector('html').dir === 'rtl';

  const handleAutoplay = useCallback(() => {
    setAutoplay(!autoplay);
  }, [autoplay]);

  const getOrder = useCallback(newSlide => {
    let order = newSlide < currentSlide ? 'prev' : 'next';

    if(newSlide === 0 && currentSlide === totalSlides - 1) {
      order = 'next';
    } else if(currentSlide === 0 && newSlide === totalSlides -1) {
      order = 'prev';
    }

    return order;
  }, [currentSlide, totalSlides]);


  const getSlideHeight = slideRef => {
    return `${slideRef.querySelector('.carousel-item-mask .background-holder').offsetHeight + slideRef.querySelector('.carousel-caption').offsetHeight}px`;
  };

  const setCarouselHeight = useCallback(slideRef => {
    if (!containerRef || !containerRef.current) {
      return;
    }

    const carouselElement = containerRef.current;
    if (window.matchMedia('(max-width: 991px)').matches) {
      carouselElement.querySelectorAll('.carousel-inner, .carousel-item-mask').forEach(container =>
        container.style.height = getSlideHeight(slideRef)
      );
    } else {
      carouselElement.querySelectorAll('.carousel-inner, .carousel-item-mask').forEach(container =>
        container.style.height = null
      );
    }
  }, [containerRef]);

  const goToSlide = useCallback((newSlide, forceCurrentSlide = false) => {
    if (!slidesRef.current) {
      return;
    }
    const nextElement = slidesRef.current[newSlide];
    const activeElement = slidesRef.current[currentSlide];

    if (nextElement && activeElement && !sliding) {
      setSliding(true);

      const order = getOrder(newSlide);
      const enterTransitionClass = options.enterTransitionClasses[order];
      const exitTransitionClass = options.exitTransitionClasses[order];

      setCarouselHeight(nextElement);

      activeElement.classList.add(exitTransitionClass);
      nextElement.classList.add(enterTransitionClass);

      const unsetTransitionClasses = () => {
        activeElement.removeEventListener('transitionend', unsetTransitionClasses);
        activeElement.classList.remove(exitTransitionClass);
        // Force to manually remove the `active` class to avoid the flicker issue
        activeElement.classList.remove(activeClass);
        nextElement.classList.remove(enterTransitionClass);
        nextElement.classList.add(activeClass);

        setCurrentSlide(newSlide);
        setSliding(false);
      };

      activeElement.addEventListener('transitionend', unsetTransitionClasses);

      // This hack is used to force what happens
      // on transitionEnd when the active slide was removed in the editor
      // (thus no longer present in the DOM)
      if (forceCurrentSlide) {
        unsetTransitionClasses();
      }
    }
  }, [currentSlide, getOrder, options, sliding, setCarouselHeight, slidesRef]);

  const goToPrevSlide = useCallback(() => {
    goToSlide(currentSlide === 0 ? totalSlides - 1 : currentSlide - 1);
  }, [currentSlide, totalSlides, goToSlide]);

  // const goToNextSlide = (autoplay = false) => {
  const goToNextSlide = useCallback(() => {
    goToSlide((currentSlide + 1 >= totalSlides) ? 0 : currentSlide + 1);
  }, [currentSlide, totalSlides, goToSlide]);

  useEffect(() => {
    if (!containerRef || !containerRef.current) {
      return;
    }

    const carouselElement = containerRef.current;
    const carouselHeadHammer = new Hammer(carouselElement, {recognizers: []});
    const hammer = new Hammer.Manager(carouselHeadHammer.element);
    const swipe = new Hammer.Swipe();
    // Only allow horizontal swiping (not vertical swiping)
    swipe.set({direction: Hammer.DIRECTION_HORIZONTAL});
    hammer.add(swipe);

    hammer.on('swipeleft', isRTL ? goToPrevSlide : goToNextSlide);
    hammer.on('swiperight', isRTL ? goToNextSlide : goToPrevSlide);

    return () => {
      hammer.off('swipeleft', isRTL ? goToPrevSlide : goToNextSlide);
      hammer.off('swiperight', isRTL ? goToNextSlide : goToPrevSlide);
    };
  }, [containerRef, currentSlide, goToNextSlide, goToPrevSlide, isRTL]);

  useEffect(() => {
    if (!containerRef || !containerRef.current) {
      return;
    }

    const currentSlideRef = slidesRef.current[currentSlide];
    if (currentSlideRef) {
      setCarouselHeight(currentSlideRef);

      window.addEventListener('resize', () => setCarouselHeight(currentSlideRef));
    }

    return () => window.removeEventListener('resize', () => setCarouselHeight(currentSlideRef));
  }, [currentSlide, setCarouselHeight, containerRef, slidesRef]);

  useEffect(() => {
    if (autoplay && totalSlides > 1) {
      if (timerRef.current) {
        clearTimeout(timerRef.current);
      }
      timerRef.current = setTimeout(() => goToNextSlide(), 5000);
      return () => clearTimeout(timerRef.current);
    } else if (timerRef.current) {
      clearTimeout(timerRef.current);
    }
  }, [totalSlides, autoplay, timerRef, goToNextSlide]);

  return {
    totalSlides,
    currentSlide,
    goToSlide,
    goToNextSlide,
    goToPrevSlide,
    handleAutoplay,
    setCurrentSlide,
    setAutoplay,
    setCarouselHeight,
    autoplay,
  };
};
