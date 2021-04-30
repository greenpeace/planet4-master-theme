import { renderSelectedVars } from './renderSelectedVars';
import { getMatchingVars } from './getMatchingVars';
import { DRAG_KEY, dragElement } from './dragElement';
import { LOCAL_STORAGE_KEY} from './VarPicker';
import { addHighlight, removeHighlight } from './highlight';
import { groupVars } from './groupVars';
import { extractPageVariables } from './extractPageVariables';

const setup = async () => {
  const editorRoot = document.createElement( 'div' );
  editorRoot.id = 'theme-editor-root';
  document.body.appendChild( editorRoot );
  dragElement( editorRoot );
  const storedLocation = localStorage.getItem(DRAG_KEY);
  try {
    const {x,y} = JSON.parse(storedLocation);
    if (x) {
      const maxX = window.outerWidth - 300;
      editorRoot.style.left = `${ Math.min(x, maxX) }px`;
    }
    if (y) {
      const maxY = window.outerHeight - 300;
      editorRoot.style.top = `${ Math.min(y, maxY) }px`;
    }
  } catch (e) {
    console.log('No position found in local storage', e)
  }

  const json = localStorage.getItem( LOCAL_STORAGE_KEY );
  try {
    const storedVars = JSON.parse(json);

    if (storedVars) {
      Object.keys(storedVars).forEach(name => {
        const value = storedVars[name];
        document.documentElement.style.setProperty(name, value);
      });
    }
  } catch (e) {
    console.log(json);
  }

  const allVars = extractPageVariables();
  const cssVars = allVars.reduce((cssVars, someVar) => [
    ...cssVars,
    ...(
      cssVars.some(v => v.name === someVar.name) ? [] : [{
        ...someVar,
        uniqueSelectors: [...new Set(someVar.usages.map(usage => usage.selector))],
      }]
    ),
  ], []);

  document.addEventListener('click', async event => {
    if (!event.altKey) {
      return;
    }
    document.documentElement.classList.add('hide-wp-admin-bar');
    event.preventDefault();

    const matchedVars = await getMatchingVars({ cssVars, target: event.target });

    const groups = await groupVars(matchedVars, event.target);

    renderSelectedVars(editorRoot, matchedVars, event.target, groups, cssVars);

    addHighlight(event.target);
    setTimeout(() => removeHighlight(event.target), 700);
  });
};

document.addEventListener( 'DOMContentLoaded', setup );
