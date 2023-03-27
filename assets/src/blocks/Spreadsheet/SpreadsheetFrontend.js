import {Component, Fragment} from '@wordpress/element';
import {ArrowIcon} from './ArrowIcon';
import {toDeclarations} from '../toDeclarations';
import {HighlightMatches} from '../HighlightMatches';
import {fetchJson} from '../../functions/fetchJson';
import {addQueryArgs} from '../../functions/addQueryArgs';

const {apiFetch} = wp;
const {__} = wp.i18n;

const placeholderData = {
  header: ['Lorem', 'Ipsum', 'Dolor'],
  rows: [
    ['Lorem', 'Ipsum', 'Dolor'],
    ['Sit', 'Amet', 'Lorem'],
    ['Amet', 'Ipsum', 'Sit'],
  ],
};

export class SpreadsheetFrontend extends Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: null,
      spreadSheetData: null,
      searchText: '',
      sortDirection: 'asc',
      sortColumnIndex: null,
    };

    this.onHeaderClick = this.onHeaderClick.bind(this);
  }

  onHeaderClick(index) {
    if (index === this.state.sortColumnIndex) {
      const newDirection = this.state.sortDirection === 'asc' ? 'desc' : 'asc';
      this.setState({
        sortDirection: newDirection,
      });
    } else {
      this.setState({
        sortColumnIndex: index,
        sortDirection: 'asc',
      });
    }
  }

  async fetchSheetData(url) {
    const sheetID = this.extractSheetID(url);

    if (sheetID !== false) {
      if (this.props.handleErrors) {
        this.props.handleErrors({invalidSheetId: false});
      }
      this.setState({loading: true});

      const args = {
        sheet_id: sheetID,
      };

      const baseUrl = document.body.dataset.nro;

      const spreadSheetData = baseUrl ?
        await fetchJson(`${baseUrl}/wp-json/${addQueryArgs('planet4/v1/get-spreadsheet-data', args)}`) :
        await apiFetch({path: addQueryArgs('planet4/v1/get-spreadsheet-data', args)});

      this.setState({loading: false, spreadSheetData});
    } else {
      if (this.props.handleErrors) {
        this.props.handleErrors({invalidSheetId: true});
      }
      this.setState({loading: false, spreadSheetData: null});
    }
  }

  async componentDidMount() {
    if (this.props.url !== '') {
      await this.fetchSheetData(this.props.url);
    }
  }

  // eslint-disable-next-line react/no-deprecated
  async componentWillReceiveProps(nextProps) {
    if (nextProps.url !== this.props.url) {
      await this.fetchSheetData(nextProps.url);
    }
  }

  extractSheetID(url) {
    const googleSheetsPattern = /https:\/\/docs\.google\.com\/spreadsheets\/d\/e\/([\w-]+)/;
    const matches = url.match(googleSheetsPattern);
    if (matches !== null) {
      return matches[1];
    }
    return false;
  }

  sortRows(rows, columnIndex) {
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
    if (this.state.sortDirection === 'desc') {
      return sortedRows.reverse();
    }
    return sortedRows;
  }

  filterMatchingRows(rows) {
    const filteredRows = rows.filter(row => {
      return row.some(cell => cell.toLowerCase().includes(this.state.searchText.toLowerCase()));
    });
    return filteredRows;
  }

  getRows() {
    if (this.state.spreadSheetData === null) {
      return placeholderData.rows;
    } else if (this.state.loading === true || this.state.loading === null) {
      return [];
    }
    return this.state.spreadSheetData.rows;
  }

  renderRows() {
    const rows = this.sortRows(this.filterMatchingRows(this.getRows()), this.state.sortColumnIndex);

    return this.state.searchText.length >= 1 && rows.length === 0 ?
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
                  this.state.searchText.length > 0 ?
                    // eslint-disable-next-line new-cap
                    HighlightMatches(cell, this.state.searchText) :
                    cell
                }
              </td>
            ))
          }
        </tr>
      ));
  }

  render() {
    const headers = this.state.spreadSheetData ?
      this.state.spreadSheetData.header :
      placeholderData.header;

    return (
      <Fragment>
        <section className={`block block-spreadsheet ${this.props.className ?? ''}`} style={{cssText: toDeclarations(this.props.css_variables)}}>
          <input className="spreadsheet-search form-control"
            type="text"
            value={this.state.searchText}
            onChange={event => this.setState({searchText: event.target.value})}
            placeholder={__('Search data', 'planet4-blocks')}
          />
          <div className="table-wrapper">
            <table className="spreadsheet-table">
              <thead>
                <tr>
                  {
                    headers.map((cell, index) => (
                      <th
                        className={(
                          index === this.state.sortColumnIndex ?
                            `spreadsheet-sorted-by sort-${this.state.sortDirection}` :
                            ''
                        )}
                        onClick={() => {
                          this.onHeaderClick(index);
                        }}
                        key={index}
                        scope="col"
                        title={cell}>
                        <button>
                          { cell }
                          <ArrowIcon />
                        </button>
                      </th>
                    ))
                  }
                </tr>
              </thead>
              <tbody>
                {
                  this.state.loading === true ?
                    <tr>
                      <td colSpan="99999">
                        <div className="spreadsheet-loading">{ __('Loading spreadsheet data…', 'planet4-blocks') }</div>
                      </td>
                    </tr> :
                    this.renderRows()
                }
              </tbody>
            </table>
          </div>
        </section>
      </Fragment>
    );
  }
}
