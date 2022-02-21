import { useEffect } from '@wordpress/element';
import { PanelBody, RadioControl, SelectControl } from '@wordpress/components';
import { getDependencyUpdates, resolveField } from '../fromThemeOptions/fromThemeOptions';
import ColorPaletteControl from '../ColorPaletteControl/ColorPaletteControl';
import { NavigationType } from '../NavigationType/NavigationType';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';

// For existing content each style options is saved in a separate post meta key, and all the names are prefixed with
// "campaign", because that used to be a thing. Hence the name "legacy fields", because eventually we want to store this
// differently.
const LEGACY_FIELDS = {
  navigationType: 'campaign_nav_type',
  navigationColor: 'campaign_nav_color',
  navigationBorder: 'campaign_nav_border',
  logo: 'campaign_logo',
  logoColor: 'campaign_logo_color',
  headingFont: 'campaign_header_primary',
  bodyFont: 'campaign_body_font',
  footerTheme: 'campaign_footer_theme',
  footerLinksColor: 'footer_links_color',
}

const themeOptions = [
  {
    value: '',
    label: 'Default',
  },
  {
    value: 'climate-new',
    label: 'Climate Emergency',
  },
  {
    value: 'forest-new',
    label: 'Forest',
  },
  {
    value: 'oceans-new',
    label: 'Oceans',
  },
  {
    value: 'plastic-new',
    label: 'Plastics',
  },

];

export const ThemeSettings = props => {
  const {
    handleThemeSwitch,
    theme,
  } = props;

  const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta'),[]);
  const { editPost } = useDispatch('core/editor');

  useEffect(() => {
    handleThemeSwitch('theme', meta.theme, meta)
  }, [meta.theme]);

  // resolveField is a cheap function so it's not a problem to call it twice.
  const getValue = fieldId => meta[fieldId] || resolveField(theme, fieldId, meta)?.default;
  const getOptions = fieldId => resolveField(theme, fieldId, meta)?.options || [];

  const updateValueAndDependencies = fieldId => value => {
    const updatedDeps = getDependencyUpdates(theme, fieldId, value, meta);
    editPost({ meta: {[fieldId]: value, ...updatedDeps} });
  }

  const navParams = {
    value: getValue(LEGACY_FIELDS.navigationType),
    setValue: updateValueAndDependencies(LEGACY_FIELDS.navigationType),
    options: getOptions(LEGACY_FIELDS.navigationType),
  };

  return <>
    <div className="components-panel__body is-opened">
      <SelectControl
        options={themeOptions}
        label={ __( 'Theme', 'planet4-blocks-backend' ) }
        value={ meta.theme }
        onChange={ value => {
          editPost({ meta: { theme: value } });
        } }
      />
    </div>
    <PanelBody
      title={ __( "Navigation", 'planet4-blocks-backend' ) }
      initialOpen={ true }
    >
      <NavigationType {...navParams} />
      <ColorPaletteControl
        value={getValue(LEGACY_FIELDS.navigationColor)}
        options={getOptions(LEGACY_FIELDS.navigationColor)}
        onChange={updateValueAndDependencies(LEGACY_FIELDS.navigationColor)}
        label={ __( 'Navigation Background Color', 'planet4-blocks-backend' ) }
        disableCustomColors
        clearable={ false }
      />
      {/* This one is actually not used anymore. Might remove later.*/}
      <RadioControl
        selected={getValue(LEGACY_FIELDS.navigationBorder)}
        options={getOptions(LEGACY_FIELDS.navigationBorder)}
        onChange={updateValueAndDependencies(LEGACY_FIELDS.navigationBorder)}
        label={ __( 'Navigation bottom border', 'planet4-blocks-backend' ) }
      />
      <SelectControl
        value={getValue(LEGACY_FIELDS.logo)}
        options={getOptions(LEGACY_FIELDS.logo)}
        onChange={updateValueAndDependencies(LEGACY_FIELDS.logo)}
        label={ __( 'Logo', 'planet4-blocks-backend' ) }
      />
      <RadioControl
        selected={getValue(LEGACY_FIELDS.logoColor)}
        options={getOptions(LEGACY_FIELDS.logoColor)}
        onChange={updateValueAndDependencies(LEGACY_FIELDS.logoColor)}
        label={ __( 'Logo Color', 'planet4-blocks-backend' ) }
        help={ __( 'Change the campaign logo color (if not default)', 'planet4-blocks-backend' ) }
      />
    </PanelBody>
    <PanelBody
      title={ __( "Fonts", 'planet4-blocks-backend' ) }
      initialOpen={ true }
    >
      <SelectControl
        value={getValue(LEGACY_FIELDS.headingFont)}
        options={getOptions(LEGACY_FIELDS.headingFont)}
        onChange={updateValueAndDependencies(LEGACY_FIELDS.headingFont)}
        label={ __( 'Header Primary Font', 'planet4-blocks-backend' ) }
      />
      <SelectControl
        value={getValue(LEGACY_FIELDS.bodyFont)}
        options={getOptions(LEGACY_FIELDS.bodyFont)}
        onChange={updateValueAndDependencies(LEGACY_FIELDS.bodyFont)}
        label={ __( 'Body Font', 'planet4-blocks-backend' ) }
      />
    </PanelBody>
    <PanelBody
      title={ __( "Footer", 'planet4-blocks-backend' ) }
      initialOpen={ true }
    >
      <RadioControl
        selected={getValue(LEGACY_FIELDS.footerTheme)}
        options={getOptions(LEGACY_FIELDS.footerTheme)}
        onChange={updateValueAndDependencies(LEGACY_FIELDS.footerTheme)}
        label={ __( 'Footer background color', 'planet4-blocks-backend' ) }
      />
      <ColorPaletteControl
        value={getValue(LEGACY_FIELDS.footerLinksColor)}
        options={getOptions(LEGACY_FIELDS.footerLinksColor)}
        onChange={updateValueAndDependencies(LEGACY_FIELDS.footerLinksColor)}
        label={ __( 'Footer links color', 'planet4-blocks-backend' ) }
        disableCustomColors
        clearable={ false }
      />
    </PanelBody>
  </>
}
