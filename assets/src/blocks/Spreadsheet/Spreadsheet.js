import { Component, Fragment } from '@wordpress/element';
import { Preview } from '../../components/Preview';
import { InspectorControls } from '@wordpress/editor';
import ColorPaletteControl from '../../components/ColorPaletteControl/ColorPaletteControl';

import {
  TextControl,
  ServerSideRender,
  PanelBody
} from '@wordpress/components';

const colors = [
  { name: 'blue', color: '#c9e7fa' },
  { name: 'green', color: '#d0fac9' },
  { name: 'grey', color: '#ececec' },
];

const colors_variables_map = {
  // Grey variables (default)
  '#ececec': {
    'spreadsheet-header-background': '#45494c',
    'spreadsheet-even-row-background': '#f5f7f8',
    'spreadsheet-odd-row-background': '#ececec'
  },
  // Green variables
  '#d0fac9': {
    'spreadsheet-header-background': '#073d14',
    'spreadsheet-even-row-background': '#eafee7',
    'spreadsheet-odd-row-background': '#d0fac9'
  },
  // Blue variables
  '#c9e7fa': {
    'spreadsheet-header-background': '#074365',
    'spreadsheet-even-row-background': '#e7f5fe',
    'spreadsheet-odd-row-background': '#c9e7fa'
  }
}

export class Spreadsheet extends Component {
  constructor(props) {
    super(props);
  }

  renderEdit() {
    const { __ } = wp.i18n;

    const { attributes, setAttributes } = this.props;

    const toAttribute = attributeName => value => {
      setAttributes( { [ attributeName ]: value } );
    };

    const toCssVariables = ( value ) => {
      setAttributes( {
        css_variables: colors_variables_map[value]
      } );
    };

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__('Setting', 'p4ge')}>
            <ColorPaletteControl
              label={__('Table Color', 'p4ge')}
              value={ attributes.css_variables['spreadsheet-odd-row-background'] }
              onChange={ toCssVariables }
              disableCustomColors
              clearable={ false }
              options= { colors }
            />
          </PanelBody>
        </InspectorControls>
        <div>
          <TextControl
            label={__('Spreadsheet URL', 'planet4-blocks-backend')}
            placeholder={__('Enter Google Spreadsheet URL', 'planet4-blocks-backend')}
            help={__(`
            From Your Google Spreadsheet Table choose File -> Publish on web.
            No need to choose the output format, any of them will work.
            If you make changes to the sheet after publishing then these changes do not always immediately get reflected,
            even when "Automatically republish when changes are made" is checked. You can force an update by unpublishing
            and republishing the sheet. This will not change the sheet's public url.
            `, 'planet4-blocks-backend')}
            value={ attributes.url }
            onChange={ toAttribute( 'url' ) }
          />
        </div>
      </Fragment>
    );
  }

  render() {
    return (
      <div>
        {
          this.props.isSelected
            ? this.renderEdit()
            : null
        }
        <Preview showBar={this.props.isSelected}>
          <ServerSideRender
            block={ 'planet4-blocks/spreadsheet' }
            attributes={ this.props.attributes }>
          </ServerSideRender>
        </Preview>
      </div>
    );
  }
}
