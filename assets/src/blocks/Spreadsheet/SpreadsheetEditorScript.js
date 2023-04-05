import {Component, Fragment} from '@wordpress/element';
import {InspectorControls} from '@wordpress/block-editor';
import ColorPaletteControl from '../../components/ColorPaletteControl/ColorPaletteControl';

import {debounce} from 'lodash';

const isNewIdentity = window.p4ge_vars.planet4_options.new_identity_styles || false;

const {__} = wp.i18n;

import {
  TextControl,
  PanelBody,
} from '@wordpress/components';

import {SpreadsheetFrontend} from './SpreadsheetFrontend';

let colors = [
  {name: 'blue', color: '#c9e7fa'},
  {name: 'green', color: '#d0fac9'},
  {name: 'grey', color: '#ececec'},
];

if (isNewIdentity) {
  colors = [
    {name: 'blue', color: '#167f82'},
    {name: 'green', color: '#1f4912'},
    {name: 'grey', color: '#45494c'},
    {name: 'gp-green',color: '#198700'},
  ];
}

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
      this.props.setAttributes({url});
    }, 300);

    this.debounceSearch = this.debounceSearch.bind(this);
  }

  handleErrors(errors) {
    this.setState(errors);
  }

  renderEdit() {
    const {attributes, setAttributes} = this.props;

    const toColorName = code => colors.find(color => color.color === code)?.name || 'grey';

    const toColorCode = name => colors.find(color => color.name === name)?.color || '#ececec';

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
            <ColorPaletteControl
              label={__('Table Color', 'planet4-blocks-backend')}
              value={toColorCode(attributes.color)}
              onChange={value => setAttributes({color: toColorName(value)})}
              disableCustomColors
              clearable={false}
              options={colors}
            />
            <TextControl
              label={__('Spreadsheet URL', 'planet4-blocks-backend')}
              placeholder={__('Enter Google Spreadsheet URL', 'planet4-blocks-backend')}
              value={this.state.url}
              onChange={url => {
                this.setState({url});
                this.debounceSearch(url);
              }}
            />
            <div className="sidebar-blocks-help">
              <ul>
                <li>
                  {/* eslint-disable-next-line @wordpress/i18n-no-collapsible-whitespace, no-restricted-syntax */}
                  { __(`From Your Google Spreadsheet Table choose File -> Publish on web.
                  No need to choose the output format, any of them will work.
                  A pop-up window will show up, click on the Publish button and then OK when the confirmation message is displayed.
                  Copy the URL that is highlighted and paste it in this block.`, 'planet4-blocks-backend') }
                </li>
                <li>
                  {/* eslint-disable-next-line @wordpress/i18n-no-collapsible-whitespace, no-restricted-syntax */}
                  { __(`If you make changes to the sheet after publishing
                    then these changes do not always immediately get reflected,
                    even when "Automatically republish when changes are made" is checked.`, 'planet4-blocks-backend') }
                </li>
                <li>
                  {/* eslint-disable-next-line @wordpress/i18n-no-collapsible-whitespace, no-restricted-syntax */}
                  { __(`You can force an update by unpublishing and republishing the sheet.
                    This will not change the sheet's public url.`, 'planet4-blocks-backend') }
                </li>
              </ul>
            </div>
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  }

  renderView() {
    const {attributes} = this.props;

    return <Fragment>
      {
        !attributes.url ?
          <div className="block-edit-mode-warning components-notice is-warning">
            { __('No URL has been specified. Please edit the block and provide a Spreadsheet URL using the sidebar.', 'planet4-blocks-backend') }
          </div> :
          null
      }

      {
        attributes.url && this.state.invalidSheetId ?
          <div className="block-edit-mode-warning components-notice is-error">
            { __('The Spreadsheet URL appears to be invalid.', 'planet4-blocks-backend') }
          </div> :
          null
      }

      <SpreadsheetFrontend {...attributes} handleErrors={this.handleErrors} />
    </Fragment>;
  }

  render() {
    return (
      <Fragment>
        {
          this.props.isSelected ?
            this.renderEdit() :
            null
        }
        { this.renderView() }
      </Fragment>
    );
  }
}
