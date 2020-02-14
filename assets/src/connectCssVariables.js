const root = document.documentElement;

const readCssVariable = name => root.style.getPropertyValue( name );

const setCssVariable = ( name, value ) => {
  root.style.setProperty( name, value );
};

const getMeta = () => {
  return wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' );
};

const metaToVariableMapping = [
  {
    metaKey: 'campaign_body_font',
    cssVariable: '--body-font',
  },
  {
    metaKey: 'campaign_header_primary',
    cssVariable: '--header-primary-font',
    transform( value ) {
      if ( value === 'Montserrat_Light' ) {
        return 'Montserrat';
      }

      if ( value ) {
        return value;
      }

      const campaignDefaults = {
        default: '"Roboto", sans-serif',
        antarctic: '"Sanctuary", sans-serif',
        arctic: '"Save the Arctic", sans-serif',
        climate: '"Jost", sans-serif',
        forest: '"Kanit", sans-serif',
        oceans: '"Montserrat", sans-serif',
        oil: '"Anton", sans-serif',
        plastic: '"Montserrat", sans-serif',
      };

      return campaignDefaults[ getMeta()[ 'theme' ] || 'default' ];
    }
  },
];

export const setUpCssVariables = () => {
  document.addEventListener( 'DOMContentLoaded', ( event ) => {
    const postType = wp.data.select( 'core/editor' ).getCurrentPostType();

    if ( postType !== 'campaign' ) {
      return;
    }

    metaToVariableMapping.forEach( mapping => {
      wp.data.subscribe( () => {
        const postMeta = getMeta();

        // wp.data starts dispatching before meta is available.
        if ( !postMeta ) {
          return;
        }

        const transform = mapping.transform || (value => value);

        const metaValue = transform( postMeta[ mapping.metaKey ] );

        const currentValue = readCssVariable( mapping.cssVariable );

        if ( currentValue !== metaValue ) {
          setCssVariable( mapping.cssVariable, metaValue );
          console.log( `Set css variable "${ mapping.cssVariable }" to "${ metaValue }"` );
        }
      } );
    } );
  } );
};
