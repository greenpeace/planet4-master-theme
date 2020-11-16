import { renderSelectedVars } from './renderSelectedVars';
import { getMatchingVars } from './getMatchingVars';
import { dragElement } from './dragElement';
import { addHighlight, LOCAL_STORAGE_KEY, removeHighlight } from './VarPicker';
import { groupVars } from './groupVars';
import { fetchJson } from '../functions/fetchJson';

const editorRoot = document.createElement( 'div' );
editorRoot.id = 'theme-editor-root';
document.body.appendChild( editorRoot );
dragElement( editorRoot );

const json = localStorage.getItem( LOCAL_STORAGE_KEY );
try {
  const storedVars = JSON.parse(json);

  if (!storedVars) {
    if (window.p4theme) {
      localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(window.p4theme));
    }
  } else {
    Object.keys(storedVars).forEach(name => {
      const value = storedVars[name];
      document.documentElement.style.setProperty(name, value);
    });
  }
} catch (e) {
  console.log(json);
}

// Create both promises first so they run in parallel, then await both in sequence.
const baseUrl = document.body.dataset.nro;
const blockVarsPromise = fetchJson(`${ baseUrl }/wp-content/plugins/planet4-plugin-gutenberg-blocks/assets/build/css_vars_merged.json`);
const themeVarsPromise = fetchJson(`${ baseUrl }/wp-content/themes/planet4-master-theme/assets/build/css_vars_merged.json`);

const setup = async () => {

  const blockVars = await blockVarsPromise;
  const themeVars = await themeVarsPromise;

  const cssVars = [...blockVars, ...themeVars].reduce((cssVars, someVar) => [
    ...cssVars,
    ...(
      cssVars.any(v => v.name === someVar.name) ? [] : [{
        ...someVar,
        uniqueSelectors: [...new Set(someVar.usages.map(usage => usage.selector))],
      }]
    ),
  ], []);

  document.addEventListener('click', async event => {
    if (!event.altKey) {
      return;
    }
    event.preventDefault();

    const matchedVars = await getMatchingVars({ cssVars, target: event.target });

    if (matchedVars.length === 0) {
      return;
    }

    const groups = await groupVars(matchedVars, event.target);

    renderSelectedVars(editorRoot, matchedVars, event.target, groups);

    addHighlight(event.target);
    setTimeout(() => removeHighlight(event.target), 700);
  });
};

export const initializeThemeEditor = () => {
  document.addEventListener( 'DOMContentLoaded', setup );
};
