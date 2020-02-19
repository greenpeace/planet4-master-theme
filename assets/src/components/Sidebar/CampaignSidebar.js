import { PluginSidebar, PluginSidebarMoreMenuItem } from "@wordpress/edit-post";
import { Component } from '@wordpress/element';
import { PanelBody, RadioControl, SelectControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import ColorPaletteControl from '../ColorPaletteControl/ColorPaletteControl';
import { withPostMeta } from '../PostMeta/withPostMeta';
import { __ } from '@wordpress/i18n';
import { fromThemeOptions } from '../fromThemeOptions/fromThemeOptions';
import isShallowEqual from '@wordpress/is-shallow-equal';

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
    };
    this.handleThemeChange = this.handleThemeChange.bind( this );
  }

  componentDidMount() {
    wp.data.subscribe( () => {
      const meta = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' );
      if (meta) {
        let theme = meta[ 'theme' ];
        if ( theme === '' ) {
          theme = 'default';
        }
        if (!isShallowEqual(this.state.meta, meta)) {
          this.setState( prevState => {
            return {
              meta: meta,
              theme: prevState.theme,
            };
          } );
          if (
            this.state.theme === null || theme !== this.state.theme.id
          ) {
            this.loadTheme( theme );
          }
        }
      }
    });
  }

  handleThemeChange( value ) {
    this.loadTheme( value );
  }

  loadTheme( value ) {
    if ( value === '' || !value ) {
      value = 'default';
    }
    const baseUrl = window.location.href.split( '/wp-admin' )[ 0 ];
    const themeJsonUrl = `${ baseUrl }/wp-content/themes/planet4-master-theme/campaign_themes/${ value }.json`;
    console.log( `fetching theme ${ value }` );
    fetch( themeJsonUrl )
      .then( response => response.json() )
      .then( json => {
        this.setState( prevState => {
          return {
            theme: json,
            meta: prevState.meta,
          };
        } );
      } );
  }

  render() {
    return (
      <>
        <PluginSidebarMoreMenuItem
          target={ CampaignSidebar.getId() }
          icon={ CampaignSidebar.getIcon()}>
          Campaign Options
        </PluginSidebarMoreMenuItem>
        <PluginSidebar
          name={ CampaignSidebar.getId() }
          title={ __( 'Campaign Options', 'planet4-blocks-backend' ) }
        >
          <div className="components-panel__body is-opened">
            <ThemeSelect
              metaKey='theme'
              label={ __( 'Theme', 'planet4-blocks-backend' ) }
              onChange={ this.handleThemeChange }
              options={ themeOptions }
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
          {/*<PanelBody*/}
          {/*  title={ __( "Colors", 'planet4-blocks-backend' ) }*/}
          {/*  initialOpen={ true }*/}
          {/*>*/}
          {/*  <ColorPalette*/}
          {/*    metaKey='campaign_header_color'*/}
          {/*    label={ __( 'Header Text Color', 'planet4-blocks-backend' ) }*/}
          {/*    disableCustomColors*/}
          {/*    clearable={ false }*/}
          {/*    theme={ this.state.theme }*/}
          {/*  />*/}
          {/*  <ColorPalette*/}
          {/*    metaKey='campaign_primary_color'*/}
          {/*    label={ __( 'Primary Button Color', 'planet4-blocks-backend' ) }*/}
          {/*    disableCustomColors*/}
          {/*    clearable={ false }*/}
          {/*    theme={ this.state.theme }*/}
          {/*  />*/}
          {/*  <ColorPalette*/}
          {/*    metaKey='campaign_secondary_color'*/}
          {/*    label={ __( 'Secondary Button Color and Link Text Color', 'planet4-blocks-backend' ) }*/}
          {/*    disableCustomColors*/}
          {/*    theme={ this.state.theme }*/}
          {/*  />*/}
          {/*</PanelBody>*/}
          <PanelBody
            title={ __( "Fonts", 'planet4-blocks-backend' ) }
            initialOpen={ true }
          >
            <Select
              metaKey='campaign_header_primary'
              label={ __( 'Header Primary Font', 'planet4-blocks-backend' ) }
              theme={ this.state.theme }
            />
            <Select
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
        </PluginSidebar>
      </>
    );
  }
}
