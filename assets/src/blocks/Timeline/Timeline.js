import {useRef, useEffect} from '@wordpress/element';

export const Timeline = props => {
  const {
    google_sheets_url,
    timenav_position,
    start_at_end,
    language,
  } = props;

  const timelineNode = useRef(null);

  const uniqueId = prefix => {
    const r = Math.floor(Math.random() * 10000);
    const t = Date.now();
    return `${prefix}-${t}-${r}`;
  };

  const setupTimeline = function() {
    timelineNode.current.id = uniqueId('timeline');

    // eslint-disable-next-line no-undef
    new TL.Timeline(timelineNode.current.id, google_sheets_url, {
      timenav_position,
      start_at_end,
      language,
    });
  };

  useEffect(
    setupTimeline,
    [
      start_at_end,
      google_sheets_url,
      timenav_position,
      language,
    ]
  );

  return <div ref={timelineNode}></div>;
};
