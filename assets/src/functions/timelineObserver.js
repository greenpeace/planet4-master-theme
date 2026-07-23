export const observeTimelineHeadings = callback => {
  const timelineSelector = '[class*="timeline"]';

  let timelineObserver;

  const startTimelineObserver = timelineBlock => {

    const existingHeading = timelineBlock.querySelector('h2');

    if (existingHeading) {
      callback();
      return;
    }

    // eslint-disable-next-line no-unused-vars
    timelineObserver = new MutationObserver(mutations => {

      const heading = timelineBlock.querySelector('h2');

      if (heading) {
        callback();
        timelineObserver.disconnect();
      }
    });

    timelineObserver.observe(timelineBlock, {
      childList: true,
      subtree: true,
    });
  };


  const timelineBlock = document.querySelector(timelineSelector);

  if (timelineBlock) {
    startTimelineObserver(timelineBlock);
  } else {
    const bodyObserver = new MutationObserver(() => {
      const timelineObserverBlock = document.querySelector(timelineSelector);

      if (timelineObserverBlock) {

        bodyObserver.disconnect();

        startTimelineObserver(timelineObserverBlock);
      }
    });

    bodyObserver.observe(document.body, {
      childList: true,
      subtree: true,
    });

    return () => {
      bodyObserver.disconnect();
      timelineObserver?.disconnect();
    };
  }

  return () => {
    timelineObserver?.disconnect();
  };
};
