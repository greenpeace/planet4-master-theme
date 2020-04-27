import { PluginSidebar, PluginSidebarMoreMenuItem } from "@wordpress/edit-post";
import { Component } from '@wordpress/element';
import { PanelBody, RadioControl, SelectControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import ColorPaletteControl from '../ColorPaletteControl/ColorPaletteControl';
import { withPostMeta } from '../PostMeta/withPostMeta';
import { withDefaultLabel } from '../withDefaultLabel/withDefaultLabel';
import { __ } from '@wordpress/i18n';
import { fromThemeOptions, getFieldFromTheme } from '../fromThemeOptions/fromThemeOptions';
import isShallowEqual from '@wordpress/is-shallow-equal';
import { savePreviewMeta } from '../../saveMetaToPreview';

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
    label: 'Climate Emergency',
  },
  {
    value: 'forest',
    label: 'Forest',
  },
  {
    value: 'oceans',
    label: 'Oceans',
  },
  {
    value: 'oil',
    label: 'Oil',
  },
  {
    value: 'plastic',
    label: 'Plastics',
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


export class CampaignSidebar extends Component {
  static getId() {
    return 'planet4-campaign-sidebar';
  }

  static getIcon() {
    return 'admin-appearance';
  }

  constructor( props ) {
    super( props );
    this.state = {
      theme: null,
      meta: null,
      parent: null,
    };
    this.handleThemeSwitch = this.handleThemeSwitch.bind( this );
    this.loadTheme = this.loadTheme.bind( this );
  }

  async handleThemeSwitch( metaKey, value, meta ) {
    await this.loadTheme( value )

    const invalidatedFields = this.state.theme.fields.filter( field => {

      const resolvedField = getFieldFromTheme(this.state.theme, field.id, meta);

      const currentValue = meta[ field.id ];

      if ( !resolvedField || !resolvedField.options ) {
        return !!currentValue;
      }

      return !(resolvedField.options.some( option => option.value === currentValue) );

    } ).map( field => getFieldFromTheme( this.state.theme, field.id, meta ) )

    return invalidatedFields.reduce( ( result, field ) => {
      return {
        ...result,
        [ field.id ]: field.default || null,
      };
    }, {
      [ metaKey ]: value,
    } );
  }

  componentDidMount() {
    wp.data.subscribe( () => {
      const meta = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' );

      if ( meta ) {
        let theme = meta[ 'theme' ];
        if ( theme === '' ) {
          theme = 'default';
        }
        if ( !isShallowEqual( this.state.meta, meta ) ) {
          this.setState( { meta: meta } );
          savePreviewMeta();
          if (
            this.state.theme === null
          ) {
            this.loadTheme( theme );
          }
        }
      }
    } );
    wp.data.subscribe( () => {
      const parentId = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'parent' ) || null;
      if (
        !this.state.parent && parentId !== null
        || (this.state.parent && parentId !== this.state.parent.id)
      ) {
        const parentPage = parentId ? wp.data.select( 'core' ).getEntityRecord( 'postType', 'campaign', parentId ) : null;
        this.setState( { parent: parentPage } );
      }
    } );
  }

  loadTheme( value ) {
    if ( value === '' || !value ) {
      value = 'default';
    }
    const baseUrl = window.location.href.split( '/wp-admin' )[ 0 ];
    const themeJsonUrl = `${ baseUrl }/wp-content/themes/planet4-master-theme/campaign_themes/${ value }.json`;
    console.log( `fetching theme ${ value }` );
    return fetch( themeJsonUrl )
      .then( response => response.json() )
      .then( json => {
        this.setState( { theme: json } );
      } );
  }

  render() {
    return (
      <>
        <PluginSidebarMoreMenuItem
          target={ CampaignSidebar.getId() }
          icon={ CampaignSidebar.getIcon() }>
          Campaign Options
        </PluginSidebarMoreMenuItem>
        <PluginSidebar
          name={ CampaignSidebar.getId() }
          title={ __( 'Campaign Options', 'planet4-blocks-backend' ) }
        >
          { this.state.parent
            ?
            <div className="components-panel__body is-opened">
              <p>{ __( 'This is a sub-page of', 'planet4-blocks-backend' ) }</p>
              <a
                href={ window.location.href.replace( /\?post=\d+/, `?post=${ this.state.parent.id }` ) }>
                { this.state.parent.title.raw }
              </a>
              <p>{ __( 'Style and analytics settings from the parent page will be used.', 'planet4-blocks-backend' ) }</p>
            </div>
            :
            <>
              <div className="components-panel__body is-opened">
                <ThemeSelect
                  metaKey='theme'
                  label={ __( 'Theme', 'planet4-blocks-backend' ) }
                  options={ themeOptions }
                  getNewMeta={ this.handleThemeSwitch }
                />
              </div>
              <PanelBody
                title={ __( "Navigation", 'planet4-blocks-backend' ) }
                initialOpen={ true }
              >
                <Radio
                  metaKey='campaign_nav_type'
                  theme={ this.state.theme }
                />
                <ColorPalette
                  metaKey='campaign_nav_color'
                  label={ __( 'Navigation Background Color', 'planet4-blocks-backend' ) }
                  disableCustomColors
                  clearable={ false }
                  theme={ this.state.theme }
                />
                <Radio
                  metaKey='campaign_nav_border'
                  label={ __( 'Navigation bottom border', 'planet4-blocks-backend' ) }
                  theme={ this.state.theme }
                />
                {
                  <Select
                    metaKey='campaign_logo'
                    label={ __( 'Logo', 'planet4-blocks-backend' ) }
                    theme={ this.state.theme }
                  />
                }
                <Radio
                  metaKey='campaign_logo_color'
                  label={ __( 'Logo Color', 'planet4-blocks-backend' ) }
                  help={ __( 'Change the campaign logo color (if not default)', 'planet4-blocks-backend' ) }
                  theme={ this.state.theme }
                />
              </PanelBody>
              {/*<PanelBody*/ }
              {/*  title={ __( "Colors", 'planet4-blocks-backend' ) }*/ }
              {/*  initialOpen={ true }*/ }
              {/*>*/ }
              {/*  <ColorPalette*/ }
              {/*    metaKey='campaign_header_color'*/ }
              {/*    label={ __( 'Header Text Color', 'planet4-blocks-backend' ) }*/ }
              {/*    disableCustomColors*/ }
              {/*    clearable={ false }*/ }
              {/*    theme={ this.state.theme }*/ }
              {/*  />*/ }
              {/*  <ColorPalette*/ }
              {/*    metaKey='campaign_primary_color'*/ }
              {/*    label={ __( 'Primary Button Color', 'planet4-blocks-backend' ) }*/ }
              {/*    disableCustomColors*/ }
              {/*    clearable={ false }*/ }
              {/*    theme={ this.state.theme }*/ }
              {/*  />*/ }
              {/*  <ColorPalette*/ }
              {/*    metaKey='campaign_secondary_color'*/ }
              {/*    label={ __( 'Secondary Button Color and Link Text Color', 'planet4-blocks-backend' ) }*/ }
              {/*    disableCustomColors*/ }
              {/*    theme={ this.state.theme }*/ }
              {/*  />*/ }
              {/*</PanelBody>*/ }
              <PanelBody
                title={ __( "Fonts", 'planet4-blocks-backend' ) }
                initialOpen={ true }
              >
                <SelectWithDefaultLabel
                  metaKey='campaign_header_primary'
                  label={ __( 'Header Primary Font', 'planet4-blocks-backend' ) }
                  theme={ this.state.theme }
                />
                <SelectWithDefaultLabel
                  metaKey='campaign_body_font'
                  label={ __( 'Body Font', 'planet4-blocks-backend' ) }
                  theme={ this.state.theme }
                />
              </PanelBody>
              <PanelBody
                title={ __( "Footer", 'planet4-blocks-backend' ) }
                initialOpen={ true }
              >
                <Radio
                  metaKey='campaign_footer_theme'
                  label={ __( 'Footer background color', 'planet4-blocks-backend' ) }
                  theme={ this.state.theme }
                />
                <ColorPalette
                  metaKey='footer_links_color'
                  label={ __( 'Footer links color', 'planet4-blocks-backend' ) }
                  disableCustomColors
                  clearable={ false }
                  theme={ this.state.theme }
                />
              </PanelBody>
            </>
          }
        </PluginSidebar>
      </>
    );
  }
}
