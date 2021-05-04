import { renderSelectedVars } from './renderSelectedVars';
import { getMatchingVars } from './getMatchingVars';
import { DRAG_KEY, dragElement } from './dragElement';
import { LOCAL_STORAGE_KEY} from './VarPicker';
import { addHighlight, removeHighlight } from './highlight';
import { groupVars } from './groupVars';
import { extractPageVariables } from './extractPageVariables';
import { filterMostSpecific } from './getOnlyMostSpecific';

const isRunningAsFrame = window.self !== window.top;

const lastRead = {};

const applyFromLocalStorage = (key) => {
  let storedVars;
  const json = localStorage.getItem( key );

  if (lastRead[key] === json) {
    return;
  }

  try {
    storedVars = JSON.parse(json);
  } catch (e) {
    console.log(json);
  }

  if (!storedVars) {
    return;
  }

  Object.keys(storedVars).forEach(name => {
    const value = storedVars[name];
    document.documentElement.style.setProperty(name, value);
  });

  const customProps = Object.entries(document.documentElement.style).filter(([, k]) => {
    return !!('string' === typeof k && k.match(/^--/));
  });

  customProps.forEach(([, k]) => {
    if (!Object.keys(storedVars).includes(k)) {
      document.documentElement.style.removeProperty(k);
    }
  });
  lastRead[key] = json;
}

export const setupThemeEditor = async (config) => {
  applyFromLocalStorage(LOCAL_STORAGE_KEY);

  if (isRunningAsFrame) {
    document.documentElement.classList.add('simulating-touch-device');
    document.documentElement.classList.add('hide-wp-admin-bar');
    const refreshLoop = () => {
      applyFromLocalStorage('theme-with-previews');
    }
    const fps = 60;
    setInterval(refreshLoop, 1000 / 60);
  }

  // Quick way to make it work with WPML. In case of NL, which doesn't have WPML, it doesn't match because without
  // WPML there is no slash at the end...
  const editorRoot = document.createElement( 'div' );

  if (!isRunningAsFrame) {
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
  }

  const allVars = await extractPageVariables();
  const cssVars = allVars.reduce((cssVars, someVar) => [
    ...cssVars,
    ...(
      cssVars.some(v => v.name === someVar.name) ? [] : [{
        ...someVar,
        uniqueSelectors: [...new Set(someVar.usages.map(usage => usage.selector))],
      }]
    ),
  ], []);

  window.addEventListener('message', event => {
    const { type, payload } = event.data;
    if (type !== 'render-vars') {
      return;
    }
    renderSelectedVars(editorRoot, payload.matchedVars, null, payload.groups, payload.rawGroups, cssVars, config);

  }, false);

  document.addEventListener('click', async event => {
    if (!event.altKey) {
      return;
    }
    document.documentElement.classList.add('hide-wp-admin-bar');
    event.preventDefault();

    const matchedVars = await getMatchingVars({ cssVars, target: event.target });

    const rawGroups = await groupVars(matchedVars, event.target);

    const groups = await filterMostSpecific(rawGroups, event.target)

    if (isRunningAsFrame) {
      window.parent.postMessage(
        {
          type: 'render-vars', payload: {
            matchedVars,
            groups: groups.map(({ element, ...rest }) => rest),
            rawGroups: rawGroups.map(({ element, ...rest }) => rest),
          }
        },
        window.location.href,
      );
    } else {
      renderSelectedVars(editorRoot, matchedVars, event.target, groups, rawGroups, cssVars, config);
    }

    addHighlight(event.target);
    setTimeout(() => removeHighlight(event.target), 700);
  });
};
