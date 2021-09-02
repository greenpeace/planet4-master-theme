import { compose } from '@wordpress/compose';
import { PanelBody, RadioControl, SelectControl } from '@wordpress/components';
import { withPostMeta } from '../PostMeta/withPostMeta';
import { fromThemeOptions } from '../fromThemeOptions/fromThemeOptions';
import { withDefaultLabel } from '../withDefaultLabel/withDefaultLabel';
import ColorPaletteControl from '../ColorPaletteControl/ColorPaletteControl';
import { __ } from '@wordpress/i18n';

const themeOptions = [
  {
    value: '',
    label: 'Default',
  },
  {
    value: 'antarctic',
    label: 'Antarctic',
  },
  {
    value: 'arctic',
    label: 'Arctic',
  },
  {
    value: 'climate',
    label: 'Climate Emergency (old)',
  },
  {
    value: 'climate-new',
    label: 'Climate Emergency (new)',
  },
  {
    value: 'forest',
    label: 'Forest (old)',
  },
  {
    value: 'forest-new',
    label: 'Forest (new)',
  },
  {
    value: 'oceans',
    label: 'Oceans (old)',
  },
  {
    value: 'oceans-new',
    label: 'Oceans (new)',
  },
  {
    value: 'oil',
    label: 'Oil',
  },
  {
    value: 'plastic',
    label: 'Plastics (old)',
  },
  {
    value: 'plastic-new',
    label: 'Plastics (new)',
  },

];

const ThemeSelect = withPostMeta( SelectControl );

const SelectWithDefaultLabel = compose(
  fromThemeOptions,
  withPostMeta,
  withDefaultLabel,
)( SelectControl );

const Select = compose(
  fromThemeOptions,
  withPostMeta,
)( SelectControl );

const Radio = compose(
  fromThemeOptions,
  withPostMeta,
)( RadioControl );

const ColorPalette = compose(
  fromThemeOptions,
  withPostMeta,
)( ColorPaletteControl );

export const LegacyThemeSettings = props => {
  const {
    handleThemeSwitch,
    theme,
  } = props;

  return <>
    <div className="components-panel__body is-opened">
      <ThemeSelect
        metaKey='theme'
        label={ __( 'Theme', 'planet4-blocks-backend' ) }
        options={ themeOptions }
        getNewMeta={ handleThemeSwitch }
      />
    </div>
    <PanelBody
      title={ __( "Navigation", 'planet4-blocks-backend' ) }
      initialOpen={ true }
    >
      <Radio
        metaKey='campaign_nav_type'
        theme={ theme }
      />
      <ColorPalette
        metaKey='campaign_nav_color'
        label={ __( 'Navigation Background Color', 'planet4-blocks-backend' ) }
        disableCustomColors
        clearable={ false }
        theme={ theme }
      />
      <Radio
        metaKey='campaign_nav_border'
        label={ __( 'Navigation bottom border', 'planet4-blocks-backend' ) }
        theme={ theme }
      />
      {
        <Select
          metaKey='campaign_logo'
          label={ __( 'Logo', 'planet4-blocks-backend' ) }
          theme={ theme }
        />
      }
      <Radio
        metaKey='campaign_logo_color'
        label={ __( 'Logo Color', 'planet4-blocks-backend' ) }
        help={ __( 'Change the campaign logo color (if not default)', 'planet4-blocks-backend' ) }
        theme={ theme }
      />
    </PanelBody>
    <PanelBody
      title={ __( "Fonts", 'planet4-blocks-backend' ) }
      initialOpen={ true }
    >
      <SelectWithDefaultLabel
        metaKey='campaign_header_primary'
        label={ __( 'Header Primary Font', 'planet4-blocks-backend' ) }
        theme={ theme }
      />
      <SelectWithDefaultLabel
        metaKey='campaign_body_font'
        label={ __( 'Body Font', 'planet4-blocks-backend' ) }
        theme={ theme }
      />
    </PanelBody>
    <PanelBody
      title={ __( "Footer", 'planet4-blocks-backend' ) }
      initialOpen={ true }
    >
      <Radio
        metaKey='campaign_footer_theme'
        label={ __( 'Footer background color', 'planet4-blocks-backend' ) }
        theme={ theme }
      />
      <ColorPalette
        metaKey='footer_links_color'
        label={ __( 'Footer links color', 'planet4-blocks-backend' ) }
        disableCustomColors
        clearable={ false }
        theme={ theme }
      />
    </PanelBody>
  </>
}
