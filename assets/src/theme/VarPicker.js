import { useEffect, useState, useRef, useMemo } from 'react';
import { VariableControl } from './VariableControl';
import { THEME_ACTIONS, useThemeEditor } from './useThemeEditor';
import { whileHoverHighlight } from './highlight';
import { exportCss, exportJson } from './export';
import { useServerThemes } from './useServerThemes';
import { useLocalStorage } from './useLocalStorage';
import { compare } from 'specificity';

export const LOCAL_STORAGE_KEY = 'p4-theme';

const byName = (a, b) => a.name > b.name ? 1 : (a.name === b.name ? 0 : -1);

const diffThemes = (themeA, themeB) => {
  const added = Object.keys(themeB).filter(k => !themeA[k]);
  const removed = Object.keys(themeA).filter(k => !themeB[k]);
  const changed = Object.keys(themeB).filter(k => !!themeA[k] && themeA[k] !== themeB[k]);
  const hasChanges = added.length > 0 || removed.length > 0 || changed.length > 0;

  return {added,removed,changed, hasChanges}
}

const diffSummary = (themeA, themeB) => {
  const { added, removed, changed } = diffThemes(themeA, themeB);

  return `Differences of your current theme to this one:
  added(${added.length}):
  ${added.map(k => `${ k }: ${ themeB[k] }`).join('\n')}
  removed(${removed.length}):
  ${removed.join('\n')}
  changed(${changed.length}):
  ${changed.map(k =>`${k}: ${themeA[k]} => ${themeB[k]}`).join('\n')}
  `;
}

const getMaxMatchingSpecificity = (usages, element) => {
  return usages.reduce((max, usage) => {
    if (!usage) {
      return max;
    }
    if (!element.matches(usage.selector)) {
      return max;
    }
    if (max === null) {
      return usage;
    }
    try {
      if (compare(max.selector, usage.selector) === -1) {
        return usage;
      }
    } catch (e) {
      console.log(e);
      return usage;
    }
    return max;
  }, null);
}

const groupByMediaQueries = (all, usage) => {
  const mediaKey = usage.media || 'all';
  const prevUsages = all[mediaKey] || [];
  const allUsages = [...prevUsages, usage]
  return ({
    ...all,
    [mediaKey]: allUsages,
  });
};

const pseudoRegex = /(:(hover|focus|active|disabled|visited))/g;
const getOnlyMostSpecific = (vars, element) => {
  // Reduce to an object, then back to array. Easier to work with for this purpose.
  const asObject = vars.reduce((all, current)=> {
    const byMediaQueries = current.usages.reduce(groupByMediaQueries, {});

    Object.entries(byMediaQueries).forEach(([media,usages]) => {
      const maxSpecific = getMaxMatchingSpecificity(usages, element) || usages[0];
      // Won't have anything added if it doesn't match
      const pseudoSuffix = (maxSpecific.selector.split(',')[0].match(pseudoRegex) || []).join('')
      const propName = usages[0].property + pseudoSuffix + media;

      if (!all[propName]) {
        all[propName] = {...current, maxSpecific};
      } else {
        const comparedUsage = getMaxMatchingSpecificity([maxSpecific, all[propName].maxSpecific], element);
        if (maxSpecific === comparedUsage) {
          all[propName] = {...current, maxSpecific};
        }
      }

    })
    return all;
  },{});
  // Map back to array.
  return Object.entries(asObject).map(([k, v]) => v);

}

const filterMostSpecific = (groups, element) => {
  const filtered = groups.map(({ vars, ...other }) => ({
    ...other,
    vars: getOnlyMostSpecific(vars, element),
  }));
  return filtered;
};

export const VarPicker = (props) => {
  const {
    groups: rawGroups,
    selectedVars,
    allVars,
    lastTarget,
  } = props;

  const [onlySpecific, setOnlySpecific] = useState(true);

  const groups = useMemo(
    () => filterMostSpecific(rawGroups, lastTarget),
    [rawGroups]
  );

  const [activeVars, setActiveVars] = useState([]);

  useEffect(() => {
    const notAlreadyActive = cssVar => !activeVars.map(active => active.name).includes(cssVar.name);

    const newOnes = selectedVars.filter(notAlreadyActive);

    setActiveVars(([...activeVars, ...newOnes]));
  }, [selectedVars]);

  const deactivate = (cssVar) => {
    const isOtherVar = active => active.name !== cssVar.name;
    setActiveVars(activeVars.filter(isOtherVar));
  };
  const closeAll = () => setActiveVars([]);
  const CloseAllButton = () => <span
    style={ {
      float: 'right',
      fontSize: '14px',
      padding: '3px 2px',
      background: 'white',
      border: '1px solid black',
      borderRadius: '4px',
      marginBottom: '8px'
    } }
    onClick={ closeAll }
  > Close all </span>;

  const [openGroups, setOpenGroups] = useState([]);
  const toggleGroup = id => {
    const newGroups = openGroups.includes(id)
      ? openGroups.filter(openId => openId !== id)
      : [...openGroups, id];

    setOpenGroups(newGroups);
  };
  useEffect(() => {
    setOpenGroups([groups[0]?.label]);
  }, [groups]);

  // Todo: save state somewhere.
  const config = {
    allVars,
  };
  const [
    {
      theme,
      defaultValues,
      hasHistory,
      hasFuture,
    },
    dispatch,
  ] = useThemeEditor(config);

  const setProperty = (name, value) => {
    dispatch({ type: THEME_ACTIONS.SET, payload: { name, value } });
  };

  const unsetProperty = (name) => {
    dispatch({ type: THEME_ACTIONS.UNSET, payload: { name } });
  };

  const [collapsed, setCollapsed] = useState(false);
  const toggleCollapsed = () => {
    setCollapsed(!collapsed);
  };

  const [shouldGroup, setShouldGroup] = useState(true);

  const [fileName, setFileName] = useLocalStorage('p4-theme-name', 'theme');

  const [
    serverThemesHeight,
    setServerThemesHeight
  ] = useLocalStorage('p4-theme-server-theme-height-list', '140px');

  useEffect(() => {
    localStorage.setItem('p4-theme-name', fileName);
  }, [fileName]);

  const activeThemeRef = useRef();

  const {
    serverThemes,
    loading: serverThemesLoading,
    uploadTheme,
    deleteTheme,
  } = useServerThemes();

  useEffect(() => {
    activeThemeRef?.current?.scrollIntoView();
  }, [serverThemes])

  const existsOnServer = serverThemes && Object.keys(serverThemes).some(t => t === fileName);

  const modifiedServerVersion = fileName
    && serverThemes[fileName]
    && diffThemes(serverThemes[fileName], theme).hasChanges

  return <div
    className='var-picker'
  >
      <span
        style={ {
          fontSize: '10px',
          border: '1px solid grey',
          borderRadius: '3px',
          margin: '0 8px',
          padding: '2px 4px',
          background: 'grey',
        } }
        onClick={ toggleCollapsed }
      >
        { collapsed ? 'show' : 'hide' }
    </span>
    { !collapsed && <label
      htmlFor=""
      onClick={ () => setOnlySpecific(!onlySpecific) }
      style={ { marginBottom: '2px' } }
    >
      <input type="checkbox" readOnly checked={ onlySpecific }/>
      { 'Show only specific properties.' }
    </label> }
    <br/>
    { !collapsed && <label
      htmlFor=""
      onClick={ () => setShouldGroup(!shouldGroup) }
      style={ { marginBottom: '2px' } }
    >
      <input type="checkbox" readOnly checked={ shouldGroup }/>
      { 'Group last clicked element' }
    </label> }
    { !collapsed && serverThemesLoading && <span>Loading server themes...</span> }
    { !collapsed && !!serverThemes && !serverThemesLoading && <ul
      onMouseUp={ event => {
        setServerThemesHeight(event.target.closest('ul').style.height);
      } }
      style={ { resize: 'vertical', height: serverThemesHeight } }
    >
      {Object.entries(serverThemes).map(([name, serverTheme]) => <li
        ref={ name === fileName ? activeThemeRef : null }
        title={diffSummary(serverTheme, theme)}
        className={'server-theme ' + (fileName === name ? 'server-theme-current' : '')}
        style={{textAlign: 'center', fontSize: '14px', height: '21px', marginBottom: '4px', clear: 'both'}}
      >
        {name} {modifiedServerVersion && name === fileName && '(*)'}
        {name !== 'default' && <button
          style={{float: 'right'}}
          onClick={ async () => {
            if (!confirm('Delete theme from server?')) {
              return;
            }
            deleteTheme(name);
          }}
        >Delete</button>}

        <button
          style={{float: 'right'}}
          onClick={() => {
            if (modifiedServerVersion && !confirm('You have some local changes that are not on the server. Cancel if you want to save changes.')) {
              return;
            }
            setFileName(name);
            dispatch({ type: THEME_ACTIONS.LOAD_THEME, payload: { theme: serverTheme } });
          }}
        >Switch</button>
      </li>)}
    </ul>}
    { !collapsed && <div
      title='Click and hold to drag'
      className="themer-controls">
      <div>
        <button
          onClick={ () => exportJson(fileName) }
        >JSON
        </button>
        <button
          onClick={ () => exportCss(fileName) }
        >CSS
        </button>
        <label style={{fontSize: '12px'}}>
          <input value={fileName} style={ { width: '130px' } } placeholder='theme' type="text"
                 onChange={ event => setFileName(event.target.value) }/>
        </label>
        <button
          title={existsOnServer ? 'Save on server' : 'Upload this theme to the server. You can upload as many as you want.'}
          style={{clear: 'both'}}
          disabled={!fileName || Object.keys(theme).length === 0}
          onClick={ async () => {
            if (existsOnServer && !confirm('Overwrite theme on server?')) {
              return;
            }
            uploadTheme(fileName, theme);
          }}
        >
          { existsOnServer ? 'Save' : 'Upload'}
        </button>
      </div>
      <div>
        <label
          // Only tested on Chrome
          style={ {
            display: 'inline-block',
            maxWidth: '33%',
            overflowX: 'hidden',
            background: 'rgba(255,255,255,.3)',
            cursor: 'copy'
          } }
        >
          <input
            type="file"
            accept={ '.json' }
            onChange={ event => {
              const reader = new FileReader();
              reader.onload = event => {
                try {
                  const theme = JSON.parse(event.target.result);
                  dispatch({ type: THEME_ACTIONS.LOAD_THEME, payload: { theme } });
                } catch (e) {
                  console.log('File contents is not valid JSON.', event.target.result, event);
                }
              };
              reader.readAsText(event.target.files[0]);
            } }
            style={ { cursor: 'copy' } }
          />
        </label>
      </div>
    </div> }
    { shouldGroup && !collapsed && <ul className={'group-list'}>
      { (onlySpecific ? groups : rawGroups).map(({ element, label, vars }) => (
        <li className={ 'var-group' } key={ label } style={ { marginBottom: '12px' } }>
          <h4
            style={ { fontWeight: 400, marginBottom: 0, cursor: 'pointer' } }
            onClick={ () => toggleGroup(label) }
            { ...whileHoverHighlight(element) }
          >
            { label } ({ vars.length })
          </h4>
          { openGroups.includes(label) && <ul>
            { vars.map(cssVar => {
                const defaultValue = defaultValues[cssVar.name];

                return <VariableControl
                  { ...{
                    theme,
                    cssVar,
                    defaultValue,
                    dispatch,
                  } }
                  key={ cssVar.name }
                  onChange={ (value) => {
                    setProperty(cssVar.name, value);
                  } }
                  onUnset={ () => unsetProperty(cssVar.name) }
                />;
              }
            ) }
          </ul> }
        </li>
      )) }
    </ul> }

    { !shouldGroup && !collapsed && <ul>
      <span>
        showing { activeVars.length } propert{ activeVars.length === 1 ? 'y' : 'ies' }
      </span>
      { activeVars.length > 0 && (
        <CloseAllButton/>
      ) }

      { activeVars.sort(byName).map(cssVar => {
          const defaultValue = defaultValues[cssVar.name];

          return <VariableControl
            { ...{
              cssVar,
              defaultValue,
              dispatch,
              theme,
            }
            }
            key={ cssVar.name }
            onUnset={ () => unsetProperty(cssVar.name) }
            onCloseClick={ deactivate }
            onChange={ value => setProperty(cssVar.name, value) }
          />;
        }
      ) }
    </ul> }
  </div>;
};
