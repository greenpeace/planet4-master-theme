import {HighlightMatches} from './HighlightMatches';
import {fetchJson} from '../../functions/fetchJson';
import {addQueryArgs} from '../../functions/addQueryArgs';

const {apiFetch} = wp;
const {useState, useEffect} = wp.element;
const {__} = wp.i18n;

const placeholderData = {
  header: ['Lorem', 'Ipsum', 'Dolor'],
  rows: [
    ['Lorem', 'Ipsum', 'Dolor'],
    ['Sit', 'Amet', 'Lorem'],
    ['Amet', 'Ipsum', 'Sit'],
  ],
};

export const SpreadsheetFrontend = ({
  url,
  color,
  setInvalidSheetId,
  className,
}) => {
  const [loading, setLoading] = useState(false);
  const [spreadsheetData, setSpreadsheetData] = useState(null);
  const [searchText, setSearchText] = useState('');
  const [sortDirection, setSortDirection] = useState('asc');
  const [sortColumnIndex, setSortColumnIndex] = useState(null);

  const onHeaderClick = event => {
    const index = parseInt(event.currentTarget.dataset.index);
    if (index === sortColumnIndex) {
      setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
    } else {
      setSortColumnIndex(index);
      setSortDirection('asc');
    }
  };

  const extractSheetID = urlParam => {
    const googleSheetsPattern = /https:\/\/docs\.google\.com\/spreadsheets\/d\/e\/([\w-]+)/;
    const matches = urlParam.match(googleSheetsPattern);
    if (matches !== null) {
      return matches[1];
    }
    return false;
  };

  useEffect(() => {
    (async () => {
      const sheetID = extractSheetID(url);

      if (sheetID !== false) {
        if (setInvalidSheetId) {
          setInvalidSheetId(false);
        }
        setLoading(true);

        const args = {
          sheet_id: sheetID,
        };

        const baseUrl = document.body.dataset.nro;

        const newSpreadsheetData = baseUrl ?
          await fetchJson(`${baseUrl}/wp-json/${addQueryArgs('planet4/v1/get-spreadsheet-data', args)}`) :
          await apiFetch({path: addQueryArgs('planet4/v1/get-spreadsheet-data', args)});

        setLoading(false);
        setSpreadsheetData(newSpreadsheetData);
      } else {
        if (setInvalidSheetId) {
          setInvalidSheetId(true);
        }
        setLoading(false);
        setSpreadsheetData(null);
      }
    })();
  }, [url, setInvalidSheetId]);

  const sortRows = (rows, columnIndex) => {
    if (columnIndex === null) {
      return rows;
    }

    // eslint-disable-next-line array-callback-return
    const sortedRows = rows.sort((rowA, rowB) => {
      const textCompare = rowA[columnIndex].localeCompare(rowB[columnIndex], undefined, {numeric: true});
      if (textCompare !== 0) {
        return textCompare;
      }
    });
    if (sortDirection === 'desc') {
      return sortedRows.reverse();
    }
    return sortedRows;
  };

  const filterMatchingRows = rows => {
    const filteredRows = rows.filter(row => {
      return row.some(cell => cell.toLowerCase().includes(searchText.toLowerCase()));
    });
    return filteredRows;
  };

  const getRows = () => {
    if (spreadsheetData === null) {
      return placeholderData.rows;
    } else if (loading === true || loading === null) {
      return [];
    }
    return spreadsheetData.rows;
  };

  const renderRows = () => {
    const rows = sortRows(filterMatchingRows(getRows()), sortColumnIndex);

    return searchText.length >= 1 && rows.length === 0 ?
      <tr>
        <td colSpan="99999">
          <div className="spreadsheet-empty-message">
            { __('No data matching your search.', 'planet4-blocks') }
          </div>
        </td>
      </tr> :
      rows.map((row, rowNumber) => (
        <tr key={rowNumber} data-order={rowNumber}>
          {
            row.map((cell, cellIndex) => (
              <td key={cellIndex}>
                {
                  searchText.length ?
                    // eslint-disable-next-line new-cap
                    HighlightMatches(cell, searchText) :
                    cell
                }
              </td>
            ))
          }
        </tr>
      ));
  };

  const headers = spreadsheetData ? spreadsheetData.header : placeholderData.header;

  return (
    <section className={`block block-spreadsheet ${className ?? ''}`}>
      <input
        name="spreadsheet-search"
        className="spreadsheet-search form-control"
        type="text"
        value={searchText}
        onChange={event => setSearchText(event.target.value)}
        placeholder={__('Search data', 'planet4-blocks')}
      />
      <div className="table-wrapper">
        <table className={`spreadsheet-table is-color-${color ?? 'grey'}`}>
          <thead>
            <tr>
              {
                headers.map((cell, index) => (
                  <th
                    className={(
                      index === sortColumnIndex ?
                        `sort-${sortDirection}` :
                        ''
                    )}
                    onClick={onHeaderClick}
                    data-index={index}
                    key={index}
                    scope="col"
                    title={cell}>
                    <button>
                      {cell}
                      <div className="arrow-icon" />
                    </button>
                  </th>
                ))
              }
            </tr>
          </thead>
          <tbody>
            {loading ?
              <tr>
                <td colSpan="99999">
                  <div className="spreadsheet-loading">{__('Loading spreadsheet data…', 'planet4-blocks')}</div>
                </td>
              </tr> :
              renderRows()
            }
          </tbody>
        </table>
      </div>
    </section>
  );
};

