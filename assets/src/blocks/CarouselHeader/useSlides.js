const {useEffect, useState} = wp.element;

const activeClass = 'active';

/**
 * Takes an array of refs to the slides
 * and performs the following transition:
 *
 * - Adds an *enter* transition class for the `next` element
 * - Adds an *exit* transition class for the `active` element
 * - Adds a listener for `ontransitionend` to the `active` element
 *
 * @param {Array}  slidesRef
 * @param {*}      lastSlide
 * @param {*}      containerRef
 * @param {Object} options
 * @return {*} functions for the carousel header slides
 */
export const useSlides = (slidesRef, lastSlide, containerRef, options = {
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
  const [currentSlide, setCurrentSlide] = useState(0);
  const [autoplayCancelled, setAutoplayCancelled] = useState(false);
  const [sliding, setSliding] = useState(false);

  const goToNextSlide = (autoplay = false) => {
    goToSlide(currentSlide === lastSlide ? 0 : currentSlide + 1);
    if (!autoplay) {
      setAutoplayCancelled(true);
    }
  };

  const goToPrevSlide = (autoplay = false) => {
    goToSlide(currentSlide === 0 ? lastSlide : currentSlide - 1);
    if (!autoplay) {
      setAutoplayCancelled(true);
    }
  };

  // eslint-disable-next-line no-shadow
  const getOrder = (currentSlide, newSlide, lastSlide) => {
    let order = newSlide < currentSlide ? 'prev' : 'next';
    if (newSlide === lastSlide && currentSlide === 0 && order !== 'prev') {
      order = 'prev';
    } else if (newSlide === 0 && currentSlide === lastSlide && order !== 'next') {
      order = 'next';
    }
    return order;
  };

  const getSlideHeight = slideRef => {
    return `${slideRef.querySelector('.carousel-item-mask .background-holder').offsetHeight + slideRef.querySelector('.carousel-caption').offsetHeight}px`;
  };

  const setCarouselHeight = slideRef => {
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
  };

  const goToSlide = (newSlide, forceCurrentSlide = false) => {
    if (!slidesRef.current) {
      return;
    }

    const nextElement = slidesRef.current[newSlide];
    const activeElement = slidesRef.current[currentSlide];

    if (newSlide !== currentSlide && nextElement && activeElement && !sliding) {
      setSliding(true);

      const order = getOrder(currentSlide, newSlide, lastSlide);
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
      };

      activeElement.addEventListener('transitionend', unsetTransitionClasses);

      // This hack is used to force what happens
      // on transitionEnd when the active slide was removed in the editor
      // (thus no longer present in the DOM)
      if (forceCurrentSlide) {
        unsetTransitionClasses();
      }
    }
  };

  useEffect(() => {
    // Update the sliding flag only when the current slide is being changed
    setSliding(false);
  }, [currentSlide]);

  return {
    currentSlide,
    goToSlide,
    goToNextSlide,
    goToPrevSlide,
    setCarouselHeight,
    autoplayCancelled,
  };
};
