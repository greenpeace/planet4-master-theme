const {useState, useEffect} = wp.element;
const {__} = wp.i18n;

const getMonthName = monthNumber => {
  return new Intl.DateTimeFormat('en', {month: 'long'})
    .format(new Date(2000, monthNumber - 1));
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

    return (
      <li className="timeline-block-event">
        <p className="timeline-block-event-day">{getMonthName(event.Month)} {event.Day}</p>
        <p className="timeline-block-event-title">{event.Headline}</p>
        <div className="timeline-description-wrapper">
          <p
            className={`timeline-block-event-description ${expanded ? 'expanded' : 'clamped'}`}
            dangerouslySetInnerHTML={{__html: event.Text}}
          />
          <button
            className="timeline-description-toggle"
            onClick={() => setExpanded(!expanded)}
          >
            {expanded ? __('Show less', 'planet4-master-theme-backend') : __('Show more', 'planet4-master-theme-backend')}
          </button>
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
                headings.push(heading.label);
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
    document.body.classList.add('new-timeline-block'); // hack to load new styles for new timeline block
    if (!sheetData) {return;}

    const grouped = sheetData.reduce((acc, item) => {
      const year = item.Year;

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

  return (
    <section className={`block timeline-block ${className ?? ''}`}>
      {!!timeline_title && !isEditing &&
        <header>
          <h2 className="page-section-header">{ timeline_title }</h2>
        </header>
      }
      {!!description && !isEditing &&
        <p className="page-section-description" dangerouslySetInnerHTML={{__html: description}} />
      }

      {loading && <p className="text-center">Loading…</p>}

      {!loading && processedSheetData && (
        <>
          {processedSheetData.map(({year, list}) => (
            <>
              <p className="timeline-block-year">{year}</p>
              <ul className="timeline-block-events">
                {list.map((event, index) => (
                  <TimelineEvent
                    key={`row-${event.Day}-${index}`}
                    event={event}
                  />
                ))}
              </ul>
            </>
          ))}
        </>
      )}
    </section>
  );
};
