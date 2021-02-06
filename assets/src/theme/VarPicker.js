import { useEffect, useState } from 'react';
import { VariableControl } from './VariableControl';
import { colorToState } from './colorToState';
import { THEME_ACTIONS, useThemeEditor } from './useThemeEditor';
import { whileHoverHighlight } from './highlight';
import { exportCss, exportJson } from './export';

export const LOCAL_STORAGE_KEY = 'p4-theme';

const byName = (a, b) => a.name > b.name ? 1 : (a.name === b.name ? 0 : -1);

const updateColorPicker = (ref, value) => {
  if (!ref?.current?.setState) {
    return;
  }

  const colors = colorToState(value);

  ref.current.setState(
    {
      ...colors,
      draftHex: colors.hex.toLowerCase(),
      draftHsl: colors.hsl,
      draftRgb: colors.rgb,
    },
  );
};

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

  const setProperty = (name, value, compoRef) => {

    dispatch({ type: THEME_ACTIONS.SET, payload: { name, value } });

    // WordPress's ColorPicker component doesn't update on a prop change, we need to trigger that here (maybe there's another way).
    updateColorPicker(compoRef, value);
  };

  const unsetProperty = (name, compoRef) => {
    dispatch({ type: THEME_ACTIONS.UNSET, payload: { name } });

    if (compoRef.current && compoRef.current.setState) {
      const colors = colorToState(defaultValues[name]);
      compoRef.current.setState(
        {
          ...colors,
          draftHex: colors.hex.toLowerCase(),
          draftHsl: colors.hsl,
          draftRgb: colors.rgb,
        },
      );
    }
  };

  const [collapsed, setCollapsed] = useState(false);
  const toggleCollapsed = () => {
    setCollapsed(!collapsed);
  };

  const [shouldGroup, setShouldGroup] = useState(true);

  const [fileName, setFileName] = useState('');

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
          <input style={ { width: '130px' } } placeholder='theme' type="text"
                 onChange={ event => setFileName(event.target.value) }/>
          .css/json
        </label>
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
    { shouldGroup && !collapsed && <ul>
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
                  onChange={ (value, compoRef = false) => {
                    setProperty(cssVar.name, value, compoRef);
                  } }
                  onUnset={ (compoRef) => unsetProperty(cssVar.name, compoRef) }
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
            onUnset={ (compoRef) => unsetProperty(cssVar.name, compoRef) }
            onCloseClick={ deactivate }
            onChange={ value => setProperty(cssVar.name, value) }
          />;
        }
      ) }
    </ul> }

  </div>;
};
