import { useState, useEffect } from '@wordpress/element';

/**
 * Takes an array of refs to the slides
 * and performs the following transition:
 * 
 * - Adds an *enter* transition class for the `next` element
 * - Adds an *exit* transition class for the `active` element
 * - Adds a listener for `ontransitionend` to the `active` element
 * 
 * @param {Array} slidesRef 
 * @param {Object} options 
 */
export const useSlides = (slidesRef, lastSlide, options = {
  // Following Bootstrap's approach for RTL: 
  // https://getbootstrap.com/docs/5.0/getting-started/rtl/#approach
  // Note: in non-directional transitions (e.g.: fade out), 
  // these could have the same class for both directions.
  enterTransitionClasses: {
    next: 'enter-from-end',
    prev: 'enter-from-start'
  },
  exitTransitionClasses: {
    next: 'exit-to-start',
    prev: 'exit-to-end'
  }
}) => {
  const [currentSlide, setCurrentSlide] = useState(0);
  const [sliding, setSliding] = useState(false);

  const goToNextSlide = () => goToSlide(currentSlide === lastSlide ? 0 : currentSlide + 1);
  const goToPrevSlide = () => goToSlide(currentSlide === 0 ? lastSlide : currentSlide - 1);

  const getOrder = (currentSlide, newSlide, lastSlide) => {
    let order = newSlide < currentSlide ? 'prev' : 'next';
    if (newSlide === lastSlide && currentSlide === 0 && order !== 'prev') {
      order = 'prev';
    } else if (newSlide === 0 && currentSlide === lastSlide && order !== 'next') {
      order = 'next';
    }
    return order;
  }
  
  const goToSlide = (newSlide, forceCurrentSlide = false) => {
    if (!slidesRef.current) {
      return;
    }

    const nextElement = slidesRef.current[newSlide];
    const activeElement = slidesRef.current[currentSlide];

    if (newSlide !== currentSlide && nextElement && activeElement && !sliding) {
      setSliding(true);

      const isRTL = false;
      const order = getOrder(currentSlide, newSlide, lastSlide);
      const enterTransitionClass = options.enterTransitionClasses[order];
      const exitTransitionClass = options.exitTransitionClasses[order];

      activeElement.classList.add(exitTransitionClass);
      nextElement.classList.add(enterTransitionClass);

      function unsetTransitionClasses() {
        activeElement.classList.remove(exitTransitionClass);
        nextElement.classList.remove(enterTransitionClass);
        activeElement.removeEventListener('transitionend', unsetTransitionClasses);
        setSliding(false);
        setCurrentSlide(newSlide);
      }
      activeElement.addEventListener('transitionend', unsetTransitionClasses);

      // This hack is used to force what happens
      // on transitionEnd when the active slide was removed in the editor
      // (thus no longer present in the DOM)
      if (forceCurrentSlide) {
        unsetTransitionClasses();
      }
    }
  }

  return {
    currentSlide,
    sliding,
    goToSlide,
    goToNextSlide,
    goToPrevSlide,
  };
}
