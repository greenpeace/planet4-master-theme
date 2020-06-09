import { renderSelectedVars } from './renderSelectedVars';
import { getMatchingVars } from './getMatchingVars';
import { dragElement } from './dragElement';
import { STORAGE_KEY } from './VarPicker';

const style = document.createElement('link')

const baseUrl = document.body.dataset.nro;

// todo: load these conditionally when logged in + permission.
style.href = `${baseUrl}/wp-includes/css/dist/components/style.css?ver=5.4.1`;
style.attributes.href = `${baseUrl}/wp-includes/css/dist/components/style.css?ver=5.4.1`;
style.rel = 'stylesheet';

document.head.appendChild(style)

const getVars = async (url) => {
  const response = await fetch(
    url );
  const raw = await response.text();
  const data = JSON.parse( raw );
  console.log( data );
  return data;
}

const editorRoot = document.createElement( 'div' );
editorRoot.id = 'theme-editor-root';
document.body.appendChild( editorRoot );
dragElement( editorRoot );

const json = localStorage.getItem( STORAGE_KEY );
try {
const storedVars = JSON.parse( json );
  if ( storedVars ) {
    Object.keys(storedVars).forEach(name=>{
      const value = storedVars[ name ];
      document.documentElement.style.setProperty( name, value );
    });
  }
} catch ( e ) {
  console.log( json );
}

const setup = async () => {
  try {
    const blockVarsPromise = getVars(`${baseUrl}/wp-content/plugins/planet4-plugin-gutenberg-blocks/assets/build/css_vars_merged.json`)
    const themeVarsPromise = getVars(`${baseUrl}/wp-content/themes/planet4-master-theme/assets/build/css_vars_merged.json`)

    const blockVars = await blockVarsPromise;
    const themeVars = await themeVarsPromise;

    const cssVars = [];

    [ ...blockVars, ...themeVars ].forEach( cssVar => {
      if ( cssVars.includes( cssVar.name ) ) {
        return;
      }
      const canHaveDuplicates = cssVar.usages.map( usage => usage.selector );
      // Create a set from the array.
      const uniqueSelectors= [ ...new Set( canHaveDuplicates ) ];
      // const selectors = uniqueSelectors.filter( selector => !selector.match( /^body(\.[\w_-]+)*$/ ) );

      cssVars.push({
        ...cssVar,
        uniqueSelectors,
      });

    } );

    document.addEventListener( 'click', async event => {
      if ( !event.altKey ) {
        return;
      }
      event.preventDefault();

      const matchedVars = await getMatchingVars( { cssVars, event } );

      if ( matchedVars.length === 0 ) {
        return;
      }

      renderSelectedVars( editorRoot, matchedVars );
    } );

  } catch ( e ) {
    console.log( e );
  }
};

export const wysiwygCssVars = () => {
  document.addEventListener( 'DOMContentLoaded', setup );
};
