import { useState, useEffect, useRef } from 'react';
import { VariableControl } from './VariableControl';
import { colorToState } from './colorToState';

export const LOCAL_STORAGE_KEY = 'p4-theme';

export const readProperty = name => {
  const value = document.documentElement.style.getPropertyValue(name);

  console.log('reading', value);

  return value;
};
const removeProperty = varName => document.documentElement.style.removeProperty(varName);

const byName = (a, b) => a.name > b.name ? 1 : (a.name === b.name ? 0 : -1);

const HIGHLIGHT_CLASS = 'theme-editor-highlight';
export const addHighlight = element => element.classList.add(HIGHLIGHT_CLASS);
export const removeHighlight = element => element.classList.remove(HIGHLIGHT_CLASS);

export const VarPicker = (props) => {
  const [refresh, setRefresh] = useState(false);
  const doRefresh = () => setRefresh(!refresh);
  const {
    groups,
    selectedVars,
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
    style={ { float: 'right', fontSize: '11px', border: '1px solid black' } }
    onClick={ closeAll }
  > Close all. </span>;

  const [openGroups, setOpenGroups] = useState([]);
  const toggleGroup = id => {
    const newGroups = openGroups.includes(id)
      ? openGroups.filter(openId => openId !== id)
      : [...openGroups, id];

    setOpenGroups(newGroups);
  };

  const [changingVars, setChangingVars] = useState({});

  const setProperty = (name, value, compoRef) => {
    console.log(`Setting property \`${ name }\` to \`${ value }\``);
    setChangingVars({
      ...changingVars,
      [name]: value,
    });
    document.documentElement.style.setProperty(name, value);

    let oldTheme;
    try {
      oldTheme = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY));
    } catch (e) {
      oldTheme = {};
    }

    const newTheme = {
      ...oldTheme,
      [name]: value,
    };

    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(newTheme));

    // Reset changing vars to the previous value. Not sure if needed?
    setChangingVars(changingVars);
    doRefresh();

    if (compoRef && compoRef.current && compoRef.current.setState) {
      const colors = colorToState(value)
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

  const unsetProperty = (name, compoRef, defaultValue) => {
    setChangingVars({
      ...changingVars,
      [name]: '',
    });
    removeProperty(name);

    let theme;
    try {
      theme = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY));
    } catch (e) {
      theme = {};
    }

    delete theme[name];

    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(theme));
    setChangingVars(changingVars);

    setTimeout(() => {
      doRefresh();
    }, 200);

    if (compoRef.current && compoRef.current.setState) {
      const colors = colorToState(defaultValue)
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
  const toggleCollapsed = () => setCollapsed(!collapsed);

  const [shouldGroup, setShouldGroup] = useState(true);

  return <div className={ 'var-picker' }>
    <span id={ 'drag-me' }>
      showing { activeVars.length } var{ activeVars.length === 1 ? '' : 's' }
    </span>
    <span
      style={ { fontSize: '10px', border: '1px solid grey', borderRadius: '3px', margin: '0 8px', padding: '2px 4px' } }
      onClick={ toggleCollapsed }
    >
        { collapsed ? 'show' : 'hide' }
    </span>
    <label
      htmlFor=""
      onClick={ () => setShouldGroup(!shouldGroup) }
      style={ { marginBottom: '2px' } }
    >
      <input type="checkbox" readOnly checked={ shouldGroup }/>
      { 'Only last clicked element' }
    </label>

    { !shouldGroup && activeVars.length > 0 && (
      <CloseAllButton/>
    ) }

    { shouldGroup && !collapsed && <ul>
      { groups.map(({ element, label, vars }) => (
        <li className={ 'var-group' } key={ label } style={ { marginBottom: '12px' } }>
          <h4
            style={{fontWeight: 400}}
            onClick={ () => toggleGroup(label) }
            onMouseEnter={ () => addHighlight(element) }
            onMouseLeave={ () => removeHighlight(element) }
          >
            { label } ({ vars.length })
          </h4>
          { openGroups.includes(label) && <ul>
            { vars.map(cssVar => {
                const defaultValue = cssVar.usages.find(usage => !!usage.defaultValue).defaultValue;

                return <VariableControl
                  {...{
                    changingVars,
                    cssVar,
                    defaultValue,
                  }}
                  key={ cssVar.name }
                  onChange={ (value, compoRef = false) => setProperty(cssVar.name, value, compoRef) }
                  onUnset={ (compoRef) => unsetProperty(cssVar.name, compoRef, defaultValue) }
                />;
              }
            ) }
          </ul> }
        </li>
      )) }
    </ul> }

    { !shouldGroup && !collapsed && <ul>
      { activeVars.sort(byName).map(cssVar => {
          const defaultValue = cssVar.usages.find(usage => !!usage.defaultValue).defaultValue;

          return <VariableControl
            { ...{
                changingVars,
              cssVar,
              defaultValue,
              }
            }
            key={ cssVar.name }
            onUnset={ (compoRef) => unsetProperty(cssVar.name, compoRef, defaultValue) }
            onCloseClick={ deactivate }
            onChange={ value => setProperty(cssVar.name, value) }
          />;
        }
      ) }
    </ul> }

  </div>;
};
