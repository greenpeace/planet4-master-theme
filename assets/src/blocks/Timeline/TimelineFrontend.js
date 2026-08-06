import {YearsNavigation} from './YearsNavigation';
import {TimelineEvent} from './TimelineEvent';

const {useState, useEffect} = wp.element;
const {__, sprintf} = wp.i18n;

/**
 * Get month name based on month number and user locale.
 *
 * @param {number} monthNumber - The month number to be formatted.
 */
const getMonthName = monthNumber => {
  const locale = document.documentElement.lang || 'en';
  return new Intl.DateTimeFormat(locale, {month: 'long'})
    .format(new Date(2000, monthNumber - 1));
};

/**
 * Get sheet id from the URL.
 *
 * @param {string} urlParam - The sheet URL.
 */
const extractSheetID = urlParam => {
  const matches = urlParam.match(/\/d\/(.+)\//);
  if (matches !== null) {
    return matches[1];
  }
  return false;
};

/**
 * Normalize a string by:
 * - Separate accent from letter.
 * - Remove diacritics.
 * - Lowercase
 * - Remove special chars
 * - Trim edges
 * - Replace spaces with _
 *
 * @param {string} str - The string to be normalized.
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

export const TimelineFrontend = ({attributes}) => {
  const {
    timeline_title,
    description,
    className,
    google_sheets_url,
    isEditing,
    timeline_id,
  } = attributes;
  const [loading, setLoading] = useState(false);
  const [sheetData, setSheetData] = useState(null);
  const [processedSheetData, setProcessedSheetData] = useState(null);

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

    const result = Object.entries(grouped).map(([year, list]) => {
      const displayDate = list.find(item => item.anchor_label?.trim())?.anchor_label?.trim();

      return {
        year,
        displayColumn: displayDate || year,
        list,
      };
    });

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

  if (loading || !processedSheetData || !timeline_id) {
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
    <section id={timeline_id} className={`block timeline-block ${className ?? ''} alignfull`} aria-label={summaryText}>
      <div className="container">
        {!!timeline_title && !isEditing &&
          <h2 className="page-section-header text-center">
            {timeline_title}
          </h2>
        }
        {!!description && !isEditing &&
          <p className="page-section-description text-center" dangerouslySetInnerHTML={{__html: description}} />
        }

        <YearsNavigation
          isEditing={isEditing}
          years={processedSheetData.map(({year, displayColumn}) => ({
            year,
            displayDate: displayColumn,
          }))}
          timelineId={timeline_id}
        />
        <fieldset className="timeline-group">
          {processedSheetData.map(({year, displayColumn, list}) => (
            <div className="timeline-block-year-group" id={`${timeline_id}-${year}`} key={`${timeline_id}-${year}`}>
              <p className="timeline-block-year">
                <span>{displayColumn}</span>
              </p>
              <ul className="timeline-block-events">
                {list.map((event, index) => (
                  <TimelineEvent
                    key={`${timeline_id}-event-${event.Day}-${index}`}
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
