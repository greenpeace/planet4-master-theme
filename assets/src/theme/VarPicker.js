import { useEffect, useState } from 'react';
import { VariableControl } from './VariableControl';
import { colorToState } from './colorToState';

export const LOCAL_STORAGE_KEY = 'p4-theme';

export const readProperty = name => document.documentElement.style.getPropertyValue(name);
const removeProperty = name => document.documentElement.style.removeProperty(name);

const byName = (a, b) => a.name > b.name ? 1 : (a.name === b.name ? 0 : -1);

const HIGHLIGHT_CLASS = 'theme-editor-highlight';
export const addHighlight = element => element.classList.add(HIGHLIGHT_CLASS);
export const removeHighlight = element => element.classList.remove(HIGHLIGHT_CLASS);

const exportJson = (fileName) => {
  const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
  const json = JSON.stringify(JSON.parse(raw), null, 2);
  const blob = new Blob([json], {type: "application/json"});
  const url  = URL.createObjectURL(blob);

  const a = document.createElement('a');
  a.download    = `${fileName || 'theme'}.json`;
  a.href        = url;
  a.textContent = "Download backup.json";
  a.click();
}

const formatCss = vars => {
  const lines = Object.keys(vars).map(k => `${ k }: ${ vars[k] };`);

  return lines.join('\n');
};

const exportCss = (fileName) => {
  const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
  const vars = JSON.parse(raw);
  const css = formatCss(vars);
  const blob = new Blob([css], {type: "application/css"});
  const url  = URL.createObjectURL(blob);

  const a = document.createElement('a');
  a.download    = `${fileName || 'theme'}.css`;
  a.href        = url;
  a.textContent = "Download backup.json";
  a.click();
}

const updateColorPicker = (ref, value) => {

  if (ref && ref.current && ref.current.setState) {
    const colors = colorToState(value)
    ref.current.setState(
      {
        ...colors,
        draftHex: colors.hex.toLowerCase(),
        draftHsl: colors.hsl,
        draftRgb: colors.rgb,
      },
    );

  }
}

const useRefresh = () => {
  const [refresh, setRefresh] = useState(false);
  return () => setRefresh(!refresh);
}

export const VarPicker = (props) => {
  const refresh = useRefresh();
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

  const [openGroups, setOpenGroups] = useState([groups[0]?.label]);
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
    // One liner copied from SO that sorts the keys in an object.
    const sorted = Object.entries(newTheme).sort().reduce( (o,[k,v]) => (o[k]=v,o), {} )

    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(sorted));

    // Reset changing vars to the previous value. Not sure if needed?
    setChangingVars(changingVars);
    refresh();

    updateColorPicker(compoRef, value);
    refresh();
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
      refresh();
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

  const [fileName, setFileName] = useState('');

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

    <div>
      <button
        onClick={ () => exportJson(fileName) }
      >JSON</button>
      <button
        onClick={ () => exportCss(fileName) }
      >CSS</button>
      <label>
        <input style={{width: '130px'}} placeholder='theme' type="text" onChange={ event => setFileName(event.target.value) }/>
        .css/json
      </label>
    </div>
    { shouldGroup && !collapsed && <ul>
      { groups.map(({ element, label, vars }) => (
        <li className={ 'var-group' } key={ label } style={ { marginBottom: '12px' } }>
          <h4
            style={{fontWeight: 400, marginBottom: 0}}
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
                  onChange={ (value, compoRef = false) => {
                    setProperty(cssVar.name, value, compoRef);
                  } }
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
