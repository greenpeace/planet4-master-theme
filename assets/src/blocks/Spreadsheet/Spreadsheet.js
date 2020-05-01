import { Component, Fragment } from '@wordpress/element';
import { Preview } from '../../components/Preview';
import { InspectorControls } from '@wordpress/editor';
import ColorPaletteControl from '../../components/ColorPaletteControl/ColorPaletteControl';

import {
  TextControl,
  ServerSideRender, PanelBody, RangeControl, ToggleControl,
} from '@wordpress/components';

export class Spreadsheet extends Component {
  constructor(props) {
    super(props);
  }

  renderEdit() {
    const { __ } = wp.i18n;

    const { color } = this.props;

    const colors = [
      { name: 'blue', color: '#C9E7FA' },
      { name: 'green', color: '#D0FAC9' },
      { name: 'grey', color: '#DCDCDC' },
    ];

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__('Setting', 'p4ge')}>
            <ColorPaletteControl
              label={__('Table Color', 'p4ge')}
              value={ color }
              onChange={ this.props.onTableColorChange }
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
            value={this.props.url}
            onChange={this.props.onUrlChange}
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
            attributes={{
              url: this.props.url,
            }}>
          </ServerSideRender>
        </Preview>
      </div>
    );
  }
}
