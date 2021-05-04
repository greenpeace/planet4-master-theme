import { useEffect, useState, useRef } from 'react';
import { VariableControl } from './VariableControl';
import { THEME_ACTIONS, useThemeEditor } from './useThemeEditor';
import { whileHoverHighlight } from './highlight';
import { exportCss, exportJson } from './export';
import { useServerThemes } from './useServerThemes';
import { useLocalStorage } from './useLocalStorage';
import { useToggle } from './useToggle';
import { ResizableFrame } from './ResizableFrame';
import { useHotkeys } from 'react-hotkeys-hook';

const hotkeysOptions = {
  enableOnTags: ['INPUT'],
}

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

export const VarPicker = (props) => {
  const {
    config,
    groups,
    rawGroups,
    selectedVars,
    allVars,
  } = props;

  const [onlySpecific, setOnlySpecific] = useState(true);

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

  const [
    {
      theme,
      defaultValues,
    },
    dispatch,
  ] = useThemeEditor({allVars});

  const [collapsed, toggleCollapsed] = useToggle(false);

  const [shouldGroup, setShouldGroup] = useState(true);

  const [fileName, setFileName] = useLocalStorage('p4-theme-name', 'theme');

  const [storedIsResponsive, setResponsive] = useLocalStorage('p4-theme-responsive', false);

  const isResponsive = !!storedIsResponsive && storedIsResponsive !== 'false';

  useHotkeys('alt+v', () => {
    setResponsive(!isResponsive);
  }, [isResponsive]);

  const [
    serverThemesHeight,
    setServerThemesHeight
  ] = useLocalStorage('p4-theme-server-theme-height-list', '140px');

  const {
    serverThemes,
    loading: serverThemesLoading,
    uploadTheme,
    deleteTheme,
  } = useServerThemes(config.serverThemes);

  const activeThemeRef = useRef();

  useEffect(() => {
    activeThemeRef?.current?.scrollIntoView();
  }, [serverThemes])

  const existsOnServer = serverThemes && Object.keys(serverThemes).some(t => t === fileName);
  const modifiedServerVersion = existsOnServer && diffThemes(serverThemes[fileName], theme).hasChanges;

  useHotkeys('alt+s', () => {
    if (fileName && fileName !== 'default' && modifiedServerVersion) {
      uploadTheme(fileName, theme);
    }
  },hotkeysOptions, [fileName, modifiedServerVersion, theme]);

  return <div
    className='var-picker'
  >
    {!!isResponsive && <ResizableFrame src={window.location.href} />}
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
    <input
      type="checkbox"
      readOnly
      checked={ shouldGroup }
      onClick={ () => { setShouldGroup(!shouldGroup); } }
    />
    { !collapsed && <label
      onClick={ () => setShouldGroup(!shouldGroup) }
      style={ { marginBottom: '2px' } }
    >
      { 'Group last clicked element' }
    </label> }
    <br/>
    <input
      type="checkbox"
      readOnly
      checked={ isResponsive }
      onClick={ () => { setResponsive(!isResponsive); } }
    />
    { !collapsed && <label
      onClick={ () => {
        setResponsive(!isResponsive);
      } }
      style={ { marginBottom: '2px' } }
    >
      { 'Responsive view' }
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

    { !collapsed && shouldGroup && <ul className={'group-list'}>
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
                  onChange={ value => {
                    dispatch({ type: THEME_ACTIONS.SET, payload: { name: cssVar.name, value } });
                  } }
                  onUnset={ () => {
                    dispatch({ type: THEME_ACTIONS.UNSET, payload: { name: cssVar.name } });
                  } }
                />;
              }
            ) }
          </ul> }
        </li>
      )) }
    </ul> }

    { !collapsed && !shouldGroup && <ul>
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
            onUnset={ () => dispatch({ type: THEME_ACTIONS.UNSET, payload: { name: cssVar.name } }) }
            onCloseClick={ deactivate }
            onChange={ value => dispatch({ type: THEME_ACTIONS.SET, payload: { name: cssVar.name, value } }) }
          />;
        }
      ) }
    </ul> }
  </div>;
};
