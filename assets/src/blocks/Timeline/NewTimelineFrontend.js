const {useState, useEffect} = wp.element;

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

  if (loading) {
    return null;
  }

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
      {sheetData && (
        <ul>
          {sheetData.map((row, rowIndex) => (
            <li key={`row-${rowIndex}`}>
              {Object.keys(row).map(key => `${key}: ${row[key]}`).join(', ')}
            </li>
          ))}
        </ul>
      )}
    </section>
  );
};
