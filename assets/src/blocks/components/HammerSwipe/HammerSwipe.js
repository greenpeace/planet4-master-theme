const {useEffect} = wp.element;

/**
 * Adds horizontal swipeb gestures to a container using Hammer.js
 *
 * @param {Object}   containerRef - Thec container element fort swipes
 * @param {Function} onNext       - Callback for swipe left
 * @param {Function} onPrev       - Callback for swipe right
 * @param {boolean}  [skip=false] - Skip setting up the swipe (e.g., isEditing)
 */
export const useHammerSwipe = (containerRef, onNext, onPrev, skip = false) => {
  const isRTL = document.querySelector('html').dir === 'rtl';

  useEffect(() => {
    if (skip || !containerRef?.current) {return;}

    let hammer;

    (async () => {
      const {default: Hammer} = await import('hammerjs/hammer.js');

      const element = containerRef.current;
      const baseHammer = new Hammer(element, {recognizers: []});
      hammer = new Hammer.Manager(baseHammer.element);

      const swipe = new Hammer.Swipe();
      swipe.set({direction: Hammer.DIRECTION_HORIZONTAL});
      hammer.add(swipe);

      hammer.on('swipeleft', isRTL ? onPrev : onNext);
      hammer.on('swiperight', isRTL ? onNext : onPrev);
    })();

    return () => {
      if (hammer) {
        hammer.off('swipeleft', isRTL ? onPrev : onNext);
        hammer.off('swiperight', isRTL ? onNext : onPrev);
      }
    };
  }, [containerRef, onNext, onPrev, skip, isRTL]);
};
