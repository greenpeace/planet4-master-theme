import {YearsNavigation} from './YearsNavigation';
import {TimelineEvent} from './TimelineEvent';
import {logDataInSentry} from '../../functions/logDataInSentry';

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
  const matches = /\/d\/(.+)\//.exec(urlParam);
  return matches === null ? null : matches[1];
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

// Build the URL to fetch the Google Sheet data in JSON format.
const URL_PREFFIX = 'https://docs.google.com/spreadsheets/d/';

const buildSheetUrl = sheetID =>
  `${URL_PREFFIX}${sheetID}/gviz/tq?&sheet=user-data&tq=Select *`;

/**
 * Remove additional text and extract only JSON data.
 *
 * @param {string} text - The raw text from the gviz response.
 */
const parseGvizResponse = text => {
  const startIndex = text.indexOf('{');
  const endIndex = text.lastIndexOf('}');
  return JSON.parse(text.substring(startIndex, endIndex + 1));
};

/**
 * Extracts normalized column headings from the gviz table columns.
 *
 * @param {Array} cols - The column definitions from the gviz response.
 */
const extractHeadings = cols =>
  (cols ?? [])
    .filter(col => col.label)
    .map(col => normalizeString(col.label));

/**
 * Safely reads the value of a gviz cell.
 *
 * @param {Object} cell - The gviz cell object.
 */
const getCellValue = cell => (cell ? cell.v : '');

/**
 * Builds a single row object by mapping each heading to its cell value.
 *
 * @param {Array}  headings - The column headings.
 * @param {Object} rowData  - The row data from the gviz response.
 */
const buildRow = (headings, rowData) =>
  Object.fromEntries(
    headings.map((heading, index) => [heading, getCellValue(rowData?.c?.[index])])
  );

/**
 * Converts a parsed gviz payload into an array of row objects.
 *
 * @param {Object} jsonData - The parsed gviz response.
 */
const transformSheetData = jsonData => {
  const headings = extractHeadings(jsonData?.table?.cols);
  return (jsonData?.table?.rows ?? []).map(rowData => buildRow(headings, rowData));
};

/**
 * Fetches and transforms the "user-data" sheet of a Google Sheet.
 *
 * @param {string} sheetID - The ID of the Google Sheet.
 */
const fetchSheetData = async sheetID => {
  const response = await fetch(buildSheetUrl(sheetID));
  const text = await response.text();
  return transformSheetData(parseGvizResponse(text));
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
    const loadSheet = async () => {
      const sheetID = extractSheetID(google_sheets_url);

      if (sheetID === null) {
        setLoading(false);
        setSheetData(null);
        return;
      }

      setLoading(true);
      try {
        setSheetData(await fetchSheetData(sheetID));
      } catch (error) {
        logDataInSentry(error);
        setSheetData(null);
      } finally {
        setLoading(false);
      }
    };

    void loadSheet();
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
                    isEditing={isEditing}
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
