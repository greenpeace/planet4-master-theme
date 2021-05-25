import { PluginSidebar, PluginSidebarMoreMenuItem } from "@wordpress/edit-post";
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { resolveField } from '../fromThemeOptions/fromThemeOptions';
import isShallowEqual from '@wordpress/is-shallow-equal';
import { savePreviewMeta } from '../../saveMetaToPreview';
import { PostParentLink } from './PostParentLink';
import { LegacyThemeSettings } from './LegacyThemeSettings';
import { NewThemeSettings } from './NewThemeSettings';

const isLegacy= theme => [
  'default',
  'antarctic',
  'arctic',
  'climate',
  'oceans',
  'oil',
  'plastic',
  'forest',
].includes(theme) || !theme;

const loadTheme = async (value) => {
  if ( value === '' || !value ) {
    value = 'default';
  }
  const withoutNew = value.replace(/-new$/, '');
  const name = isLegacy(withoutNew) ? withoutNew : 'default';
  const baseUrl = window.location.href.split( '/wp-admin' )[ 0 ];
  const themeJsonUrl = `${ baseUrl }/wp-content/themes/planet4-master-theme/campaign_themes/${ name }.json`;

  const json = await fetch(themeJsonUrl);
  return await json.json();
}

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
  }

  // When theme switches, we need to check if any options were previously chosen that are not allowed in the new theme.
  // For each of these, we either set them to the default value
  async handleThemeSwitch( metaKey, value, meta ) {
    const newTheme = await loadTheme( value )
    const prevTheme = this.state.theme;
    this.setState({ theme: newTheme });

    // Loop through the new theme's fields, and check whether any of the already chosen options has a value that is not
    // available anymore.
    const invalidatedFields = prevTheme.fields.filter( field => {

      const resolvedField = resolveField(newTheme, field.id, meta);

      const currentValue = meta[ field.id ];

      if ( !resolvedField || !resolvedField.options ) {
        return !!currentValue;
      }

      return !(resolvedField.options.some( option => option.value === currentValue) );

    } ).map( field => resolveField( newTheme, field.id, meta ) )

    // Set each of the invalidated fields to their default value, or unset them.
    return invalidatedFields.reduce( ( result, field ) => {
      // Adding this check to prevent a crash. Probably the previous code can be rewritten to not produce null, but
      // that would probably cascade into many changes and this is code we'll probably remove soon.
      if (!field) {
        return result;
      }
      return {
        ...result,
        [ field.id ]: field.default || null,
      };
    }, {
      [ metaKey ]: value,
    } );
  }

  componentDidMount() {
    wp.data.subscribe(async () => {
      const meta = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' );

      if (!meta) {
        return;
      }
      let themeName = meta['theme'];
      if (themeName === '') {
        themeName = 'default';
      }
      if (isShallowEqual(this.state.meta, meta)) {
        return;
      }
      this.setState({ meta });
      savePreviewMeta();
      if (
        this.state.theme === null
      ) {
        const theme = await loadTheme(themeName);
        this.setState({ theme: theme });
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

  render() {
    const { parent, theme, meta } = this.state;

    const isLegacyTheme = !theme || isLegacy(theme.id);

    return (
      <>
        <PluginSidebarMoreMenuItem
          target={ CampaignSidebar.getId() }
          icon={ CampaignSidebar.getIcon() }>
          Campaign Options
        </PluginSidebarMoreMenuItem>
        <PluginSidebar
          name={ CampaignSidebar.getId() }
          title={ __('Campaign Options', 'planet4-blocks-backend') }
        >
          { !!parent && <PostParentLink parent={ parent }/> }
          { !parent && meta && <NewThemeSettings currentTheme={meta.theme} onChange={ async value => {
            const theme = await loadTheme(value);
            this.setState({ theme });
          } }/> }
          { !parent && <LegacyThemeSettings
            theme={ theme }
            handleThemeSwitch={ this.handleThemeSwitch }
            isLegacyTheme={isLegacyTheme}
          /> }
        </PluginSidebar>
      </>
    );
  }
}
