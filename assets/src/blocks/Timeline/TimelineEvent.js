const {useState} = wp.element;
const useSelect = wp.data?.useSelect;
const SandBox = wp.components?.SandBox;
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
 * Get the embeddable YouTube URL for an event's media.
 *
 * @param {Object} event - The event to be checked.
 * @return {string|null} - The embeddable URL
 */
const getYoutubeEmbedUrl = event => {
  const match = event.media.match(
    /(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/i
  );
  return match ? `https://www.youtube.com/embed/${match[1]}` : null;
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

/**
 * Check if an event has a YouTube URL.
 *
 * @param {Object} event - The event to be checked.
 */
const hasYoutube = event => event.media && /^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/|shorts\/)|youtu\.be\/)/i.test(event.media);

export const TimelineEvent = ({event, isEditing}) => {
  const [expanded, setExpanded] = useState(false);
  const contentId = `timeline-content-${event.day}-${event.month}`;

  /**
   * The oEmbed preview HTML for the YouTube video.
   * This is necessary in the editor, as a simple iframe doesn't work.
   *
   * @type {string|null}
   */
  const youtubeHtml = isEditing && useSelect ?
    useSelect(
      select => {
        const url = hasYoutube(event) ? event.media : null;
        if (!url) {return null;}

        const {getEmbedPreview, hasFinishedResolution} = select('core');
        const preview = getEmbedPreview(url);
        const hasResolved = hasFinishedResolution('getEmbedPreview', [url]);

        if (!hasResolved || !preview || preview.html === false) {return null;}

        return preview.html;
      },
      [event.media, isEditing]
    ) :
    null;

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
      {hasYoutube(event) && (
        isEditing ?
          (youtubeHtml && SandBox && (
            <SandBox
              html={youtubeHtml}
              title={event.media_caption ?? ''}
              type="embed"
              allowSameOrigin
              styles={['body, div, iframe { width: 100%; height: 100%; overflow: hidden; }']}
            />
          )) :
          (
            <iframe
              src={getYoutubeEmbedUrl(event)}
              title={event.media_caption ?? ''}
              loading="lazy"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowFullScreen
            />
          )
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
          data-ga-category="Timeline"
          data-ga-action="Show more"
          data-ga-label="n/a"
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
