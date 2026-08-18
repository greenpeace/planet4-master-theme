import {debounce} from '@wordpress/compose';

const DEBOUNCE_MS = 150;

export const observeTimelineHeadings = callback => {
  const timelineSelector = '[class*="timeline"]';

  const observers = [];
  const watchedBlocks = new WeakSet();

  const debouncedCallback = debounce(callback, DEBOUNCE_MS);

  const startTimelineObserver = timelineBlock => {
    if (watchedBlocks.has(timelineBlock)) {
      return;
    }
    watchedBlocks.add(timelineBlock);

    const existingHeading = timelineBlock.querySelector('h2');

    if (existingHeading) {
      debouncedCallback();
      return;
    }

    const timelineObserver = new MutationObserver(() => {
      const heading = timelineBlock.querySelector('h2');

      if (heading) {
        debouncedCallback();
        timelineObserver.disconnect();
      }
    });

    timelineObserver.observe(timelineBlock, {
      childList: true,
      subtree: true,
    });

    observers.push(timelineObserver);
  };

  const attachToAllTimelineBlocks = () => {
    document.querySelectorAll(timelineSelector).forEach(startTimelineObserver);
  };

  // Watch the body for as long as this component is mounted, since timeline
  // blocks can keep hydrating in at different times. Each newly-inserted
  // block gets picked up and attached here; already-watched blocks are
  // skipped via `watchedBlocks`.
  const bodyObserver = new MutationObserver(attachToAllTimelineBlocks);

  bodyObserver.observe(document.body, {
    childList: true,
    subtree: true,
  });

  // Attach to whatever's already in the DOM immediately too.
  attachToAllTimelineBlocks();

  return () => {
    debouncedCallback.cancel();
    bodyObserver.disconnect();
    observers.forEach(observer => observer.disconnect());
  };
};
