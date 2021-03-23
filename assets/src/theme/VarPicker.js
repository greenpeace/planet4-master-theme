import { useEffect, useState } from 'react';
import { VariableControl } from './VariableControl';
import { colorToState } from './colorToState';
import { THEME_ACTIONS, useThemeEditor } from './useThemeEditor';
import { whileHoverHighlight } from './highlight';
import { exportCss, exportJson } from './export';
import { fetchJson } from '../functions/fetchJson';
import { addQueryArgs } from '../functions/addQueryArgs';

export const LOCAL_STORAGE_KEY = 'p4-theme';

const byName = (a, b) => a.name > b.name ? 1 : (a.name === b.name ? 0 : -1);

const uploadTheme = async (name, theme) => {
  return wp.apiFetch({
    path: 'planet4/v1/add-theme/',
    method: 'POST',
    data: {
      name,
      theme,
    }
  });
}

const deleteTheme = async (name) => {
  return wp.apiFetch({
    path: 'planet4/v1/delete-theme/',
    method: 'POST',
    data: {
      name,
    }
  });
}

const diffSummary = (themeA, themeB) => {
  const added = Object.keys(themeB).filter(k => !themeA[k]);
  const removed = Object.keys(themeA).filter(k => !themeB[k]);
  const changed = Object.keys(themeB).filter(k => !!themeA[k] && themeA[k] !== themeB[k]);

  return `Differences of your current theme to this one:
  added(${added.length}):
  ${added.map(k => `${ k }: ${ themeB[k] }`).join('\n')}
  removed(${removed.length}):
  ${removed.join('\n')}
  changed(${changed.length}):
  ${changed.map(k =>`${k}: ${themeA[k]} => ${themeB[k]}`).join('\n')}
  `;
}

const useServerThemes = (refresh) => {
  const [serverThemes, setServerThemes] = useState([]);
  useEffect(() => {
    const doApiCall = async () => {
      const themes = await wp.apiFetch({
        path: 'planet4/v1/themes/',
        method: 'GET',
      });
      setServerThemes(themes);
    }
    doApiCall();
  }, [refresh]);

  return [
    serverThemes,
  ];
}

export const VarPicker = (props) => {
  const {
    groups,
    selectedVars,
    allVars,
  } = props;

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

  const [fileName, setFileName] = useState('');

  const [themesDirty, setThemesDirty] = useState(false);

  const [serverThemes] = useServerThemes(themesDirty);

  const existsOnServer = serverThemes && Object.keys(serverThemes).some(t => t === fileName);

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
      onClick={ () => setShouldGroup(!shouldGroup) }
      style={ { marginBottom: '2px' } }
    >
      <input type="checkbox" readOnly checked={ shouldGroup }/>
      { 'Group last clicked element' }
    </label> }
    { !collapsed && !!serverThemes && <ul style={{maxHeight: '140px'}}>
      {Object.entries(serverThemes).map(([name, serverTheme]) => <li
        title={diffSummary(serverTheme, theme)}
        style={{textAlign: 'center', fontSize: '14px',height: '21px', marginBottom: '4px', clear: 'both', background: fileName === name ? 'green' : 'white'}}
      >
        {name}
        <button
          style={{float: 'right'}}
          onClick={ async () => {
            if (!confirm('Delete theme from server?')) {
              return;
            }
            await deleteTheme(name);
            setThemesDirty(!themesDirty);
          }}
        >Delete</button>
        <button
          style={{float: 'right'}}
          onClick={() => {
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
          title={'Share this theme on the server. For now you cannot update a theme, but you can delete and re-add it.'}
          style={{clear: 'both'}}
          disabled={!fileName || Object.keys(theme).length === 0}
          onClick={ async () => {
            if (existsOnServer && !confirm('Overwrite theme on server?')) {
              return;
            }
            await uploadTheme(fileName, theme);
            setThemesDirty(!themesDirty);
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
      { groups.map(({ element, label, vars }) => (
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
