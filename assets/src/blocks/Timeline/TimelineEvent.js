const {useState} = wp.element;
const {__} = wp.i18n;

/**
 * Get event date in correct format based on user locale.
 *
 * @param {number} day   - The day of the event.
 * @param {number} month - The month of the event.
 */
const getLocalizedDate = (day, month) => {
  const locale = document.documentElement.lang || 'en';

  const date = new Date(2000, month - 1, day);

  return new Intl.DateTimeFormat(locale, {
    day: 'numeric',
    month: 'long',
  }).format(date);
};

/**
 * Check if a string is a valid URL that uses https protocol.
 *
 * @param {string} value - The URL to be checked.
 */
const isValidHttpsUrl = value => {
  if (typeof value !== 'string') {return false;}

  try {
    const url = new URL(value.trim());

    return (
      url.protocol === 'https:' &&
      url.hostname.includes('.') &&
      !url.hostname.startsWith('.')
    );
  } catch {
    return false;
  }
};

/**
 * Check if an event has an image.
 *
 * @param {Object} event - The event to be checked.
 */
const hasImage = event => event.media && /\.(jpg|jpeg|png|webp|avif|gif|svg)(\?.*)?$/i.test(event.media);

/**
 * Check if an event has a video.
 *
 * @param {Object} event - The event to be checked.
 */
const hasVideo = event => event.media && /\.(mp4|webm)(\?.*)?$/i.test(event.media);

export const TimelineEvent = ({event}) => {
  const [expanded, setExpanded] = useState(false);
  const contentId = `timeline-content-${event.day}-${event.month}`;

  return (
    <li className="timeline-block-event">
      <p
        className="timeline-block-event-day"
        aria-label={`${getLocalizedDate(event.day, event.month)}`}
      >
        {getLocalizedDate(event.day, event.month)}
      </p>
      {hasImage(event) && (
        <img
          src={event.media}
          alt={event.media_caption ?? ''}
          loading="lazy"
          onError={e => e.currentTarget.style.display = 'none'}
        />
      )}
      {hasVideo(event) && (
        <video controls>
          <source
            src={event.media}
            loading="lazy"
            onError={e => e.currentTarget.style.display = 'none'}
          />
        </video>
      )}
      <h3 className="timeline-block-event-title">{event.headline}</h3>
      <div className="timeline-description-wrapper">
        <p
          id={contentId}
          className={`timeline-block-event-description ${expanded ? 'expanded' : 'clamped'}`}
          dangerouslySetInnerHTML={{__html: event.text}}
        />
        <button
          className="timeline-description-toggle"
          aria-expanded={expanded}
          aria-controls={contentId}
          onClick={() => setExpanded(!expanded)}
          type="button"
        >
          {expanded ? __('Show less', 'planet4-blocks') : __('Show more', 'planet4-blocks')}
        </button>
        {event.external_link && isValidHttpsUrl(event.external_link)  && (
          <div className="d-flex justify-content-end">
            <a
              target="_blank"
              href={event.external_link} rel="noreferrer"
              className="timeline-external-link"
            >
              {__('Learn more', 'planet4-blocks')}
            </a>
          </div>
        )}
      </div>
    </li>
  );
};
