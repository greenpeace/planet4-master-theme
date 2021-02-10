import { Component, Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import ColorPaletteControl from '../../components/ColorPaletteControl/ColorPaletteControl';

import { debounce } from 'lodash';

import {
  TextControl,
  PanelBody
} from '@wordpress/components';

import { SpreadsheetFrontend } from './SpreadsheetFrontend';

const colors = [
  { name: 'blue', color: '#c9e7fa' },
  { name: 'green', color: '#d0fac9' },
  { name: 'grey', color: '#ececec' },
];

const colors_variables_map = {
  // Grey variables (default)
  '#ececec': {
    'block-spreadsheet--header--background-color': '#45494c',
    'block-spreadsheet--even-row--background': '#f5f7f8',
    'block-spreadsheet--odd-row--background': '#ececec'
  },
  // Green variables
  '#d0fac9': {
    'block-spreadsheet--header--background-color': '#073d14',
    'block-spreadsheet--even-row--background': '#eafee7',
    'block-spreadsheet--odd-row--background': '#d0fac9'
  },
  // Blue variables
  '#c9e7fa': {
    'block-spreadsheet--header--background-color': '#074365',
    'block-spreadsheet--even-row--background': '#e7f5fe',
    'block-spreadsheet--odd-row--background': '#c9e7fa'
  }
};

export class SpreadsheetEditor extends Component {
  constructor(props) {
    super(props);
    this.handleErrors = this.handleErrors.bind(this);
    this.state = {
      invalidSheetId: false,
      errorFetchingSpreadsheet: false,
      url: props.attributes.url,
    };

    this.debounceSearch = debounce(url => {
      this.props.setAttributes( { url } );
    }, 300);

    this.debounceSearch = this.debounceSearch.bind(this)
  }

  handleErrors( errors ) {
    this.setState( errors );
  }

  renderEdit() {
    const {__} = wp.i18n;

    const { attributes, setAttributes } = this.props;

    const toCssVariables = ( value ) => {
      setAttributes( {
        css_variables: colors_variables_map[value]
      } );
    };

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
            <ColorPaletteControl
              label={__('Table Color', 'planet4-blocks-backend')}
              value={ attributes.css_variables['block-spreadsheet--odd-row--background'] }
              onChange={ toCssVariables }
              disableCustomColors
              clearable={ false }
              options= { colors }
            />
            <TextControl
              label={__('Spreadsheet URL', 'planet4-blocks-backend')}
              placeholder={__('Enter Google Spreadsheet URL', 'planet4-blocks-backend')}
              value={ this.state.url }
              onChange={
                url => {
                  this.setState({ url });
                  this.debounceSearch( url );
                }
              }
            />
            <div className="sidebar-blocks-help">
              <ul>
                <li>
                  { __(`From Your Google Spreadsheet Table choose File -> Publish on web.
                  No need to choose the output format, any of them will work.
                  A pop-up window will show up, click on the Publish button and then OK when the confirmation message is displayed.
                  Copy the URL that is highlighted and paste it in this block.`, 'planet4-blocks-backend') }
                </li>
                <li>
                  { __(`If you make changes to the sheet after publishing
                    then these changes do not always immediately get reflected,
                    even when "Automatically republish when changes are made" is checked.`, 'planet4-blocks-backend') }
                </li>
                <li>
                  { __(`You can force an update by unpublishing and republishing the sheet.
                    This will not change the sheet's public url.`, 'planet4-blocks-backend' ) }
                </li>
              </ul>
            </div>
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  }

  renderView() {
    const {__} = wp.i18n;
    const { attributes } = this.props;

    return <Fragment>
      {
        ! attributes.url
        ? <div className="block-edit-mode-warning components-notice is-warning">
            { __( 'No URL has been specified. Please edit the block and provide a Spreadsheet URL using the sidebar.', 'planet4-blocks' ) }
          </div>
        : null
      }

      {
        attributes.url && this.state.invalidSheetId
        ? <div className="block-edit-mode-warning components-notice is-error">
            { __( 'The Spreadsheet URL appears to be invalid.', 'planet4-blocks' ) }
          </div>
        : null
      }

      <SpreadsheetFrontend { ...attributes } handleErrors={ this.handleErrors } />
    </Fragment>;
  }

  render() {
    return (
      <Fragment>
        {
          this.props.isSelected
            ? this.renderEdit()
            : null
        }
        { this.renderView() }
      </Fragment>
    );
  }
}
