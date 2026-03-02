import {YearsNavigation} from './YearsNavigation';

const {useState, useEffect} = wp.element;
const {__, sprintf} = wp.i18n;

const getMonthName = monthNumber => {
  const locale = document.documentElement.lang || 'en';
  return new Intl.DateTimeFormat(locale, {month: 'long'})
    .format(new Date(2000, monthNumber - 1));
};

const getLocalizedDate = (day, month) => {
  const locale = document.documentElement.lang || 'en';

  const date = new Date(2000, month - 1, day);

  return new Intl.DateTimeFormat(locale, {
    day: 'numeric',
    month: 'long',
  }).format(date);
};

/*
  Check if a string is a valid URL that uses https protocol
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

export const NewTimelineFrontend = ({attributes}) => {
  const {
    timeline_title,
    description,
    className,
    google_sheets_url,
    isEditing,
  } = attributes;
  const [loading, setLoading] = useState(false);
  const [sheetData, setSheetData] = useState(null);
  const [processedSheetData, setProcessedSheetData] = useState(null);


  const TimelineEvent = ({event}) => {
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


  const extractSheetID = urlParam => {
    const matches = urlParam.match(/\/d\/(.+)\//);
    if (matches !== null) {
      return matches[1];
    }
    return false;
  };

  /*
  Normalize a string by:
  - Separate accent from letter.
  - Remove diacritics.
  - Lowercase
  - Remove special chars
  - Trim edges
  - Replace spaces with _
  */
  const normalizeString = str => {
    return str
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9\s]/g, '')
      .trim()
      .replace(/\s+/g, '_');
  };

  useEffect(() => {
    (async () => {
      const sheetID = extractSheetID(google_sheets_url);
      if (sheetID !== false) {
        setLoading(true);

        const url = `https://docs.google.com/spreadsheets/d/${sheetID}/gviz/tq?&sheet=user-data&tq=Select *`;

        fetch(url)
          .then(response => response.text())
          .then(data => {
            // Remove additional text and extract only JSON data.
            const startIndex = data.indexOf('{');
            const endIndex = data.lastIndexOf('}');
            const jsonString = data.substring(startIndex, endIndex + 1);
            const jsonData = JSON.parse(jsonString);

            const headings = [];
            // Get headings.
            jsonData?.table?.cols?.forEach(heading => {
              if (heading.label) {
                headings.push(normalizeString(heading.label));
              }
            });

            // Extract rows data.
            const newData = [];
            jsonData?.table?.rows?.forEach(rowData => {
              const row = {};
              headings.forEach((heading, index) => row[heading] = (rowData?.c[index] !== null) ? rowData?.c[index].v : '');
              newData.push(row);
            });
            setSheetData(newData);
            setLoading(false);
          });
      } else {
        setLoading(false);
        setSheetData(null);
      }
    })();
  }, [google_sheets_url]);

  // Format sheetData for frontend rendering
  useEffect(() => {
    if (!sheetData) {return;}

    const grouped = sheetData.reduce((acc, item) => {
      const year = item.year;

      if (!acc[year]) {
        acc[year] = [];
      }

      acc[year].push(item);
      return acc;
    }, {});

    const result = Object.entries(grouped)
      .map(([year, list]) => ({
        year,
        list,
      }));

    setProcessedSheetData(result);
  }, [sheetData]);

  let total = 0;
  let firstDate = '';
  let lastDate = '';

  if (processedSheetData) {
    const allEvents = processedSheetData.flatMap(y => y.list);
    total = allEvents.length;

    if (allEvents.length > 0) {
      const first = allEvents[0];
      const last = allEvents[allEvents.length - 1];

      firstDate = `${getMonthName(first.month)} ${first.year}`;
      lastDate = `${getMonthName(last.month)} ${last.year}`;
    }
  }

  if (loading || !processedSheetData) {
    return null;
  }

  const summaryText = sprintf(
  /* translators: 1: timeline title, 2: total items, 3: first date, 4: last date */
    __('%1$s, %2$d items from %3$s to %4$s.', 'planet4-blocks'),
    timeline_title,
    total,
    firstDate,
    lastDate
  );

  return (
    <section className={`block timeline-block new-timeline-block ${className ?? ''} alignfull`} aria-label={summaryText}>
      <div className="container">
        {!!timeline_title && !isEditing &&
          <h2 className="page-section-header text-center">
            {timeline_title}
          </h2>
        }
        {!!description && !isEditing &&
          <p className="page-section-description text-center" dangerouslySetInnerHTML={{__html: description}} />
        }

        <YearsNavigation years={processedSheetData.map(({year}) => year)} />
        <fieldset className="timeline-group">
          {processedSheetData.map(({year, list}) => (
            <div id={year} key={year}>
              <p className="timeline-block-year">{year}</p>
              <ul className="timeline-block-events">
                {list.map((event, index) => (
                  <TimelineEvent
                    key={`row-${event.Day}-${index}`}
                    event={event}
                  />
                ))}
              </ul>
            </div>
          ))}
        </fieldset>
      </div>
    </section>
  );
};
