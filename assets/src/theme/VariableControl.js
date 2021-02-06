import { useState, useRef} from 'react';
import { Fragment} from '@wordpress/element';
import { COLOR_VALUE_REGEX, TypedControl } from './typedControl';
import { IconButton } from '@wordpress/components';
import { PSEUDO_REGEX, THEME_ACTIONS} from './useThemeEditor';

const uniqueUsages = cssVar => {
  return [
    ...new Set(
      cssVar.usages.map(
        usage => usage.selector.replace(',', ',\n')
      )
    )
  ];
};

const capitalize = string => string.charAt(0).toUpperCase() + string.slice(1);
const format = name => {
  // todo: make this make more sense
  const raw = name.replace(/^--/, '').replace(/--/g, ': ').replace(/[-_]/g, ' ')
  const parts = raw.split(':');

  return [
    parts.slice(0, parts.length - 1).join(':') + ':',
    parts[parts.length - 1].trim().replace(/ /g, '-')
  ];
};
const formatTitle = (cssVar, isRepeat) => {
  const [prefix, prop] = format(cssVar.name);
  return <Fragment>
    <span style={ { fontWeight: 'bold', color: isRepeat ? 'grey' : 'black' } }>{capitalize(prefix)}</span>
    <br/>
    <span>{prop}</span>
  </Fragment>;
};

const previewValue = (value, cssVar, onClick, isDefault) => {
  const size = '30px';

  const title = `${value}${ !isDefault ? '' : ' (default)' }`;

  if (value && `${value}`.match(COLOR_VALUE_REGEX)) {
    return <span
      key={ 1 }
      onClick={ onClick }
      title={ title }
      style={ {
        width: size,
        height: size,
        border: '1px solid black',
        borderRadius: '6px',
        backgroundColor: value,
        float: 'right',
        marginTop: '7px',
      } }/>;
  }

  return <span
    key={ 1 }
    onClick={ onClick }
    title={ title }
    style={ {
      // width: size,
      fontSize: '14px',
      height: size,
      float: 'right',
      marginTop: '2px'
    } }
  >
      { value }
    </span>;
}

const showUsages = (cssVar, showSelectors, toggleSelectors) => {
  const renderCollapsed = () => <pre onClick={ toggleSelectors } className={ 'usages-collapsed' }>
      { uniqueUsages(cssVar).join(', ') }
    </pre>;

  const renderShow = () => <pre onClick={ toggleSelectors }>
        { uniqueUsages(cssVar).join('\n').replace(',', ',\n') }
      </pre>;

  return <div
    style={ { fontSize: '11px', position: 'relative', marginTop: '16px' } }
  >
      <span
        key={ 3 }
        onClick={ toggleSelectors }
        style={ {
          userSelect: 'none',
          fontSize: '10px',
          position: 'absolute',
          top: -12,
          left: 0
        } }
      >
        { uniqueUsages(cssVar).length } selectors
      </span>
    { showSelectors ? renderShow() : renderCollapsed() }

  </div>;
}

export const VariableControl = (props) => {
  const {
    theme,
    cssVar,
    onCloseClick,
    onChange,
    onUnset,
    defaultValue,
    isRepeat = false,
    dispatch,
  } = props;

  const [
    isOpen, setIsOpen
  ] = useState(false);

  const toggleOpen = () => setIsOpen(!isOpen);

  const [
    showSelectors, setShowSelectors
  ] = useState(false);

  const toggleSelectors = () => setShowSelectors(!showSelectors)
  const compoRef = useRef();
  // The theme ensures that if the property is not returned, it can be safely read from the window.
  const value = theme[cssVar.name] || defaultValue;
  const isDefault = value === defaultValue;

  return <li
    key={ cssVar.name }
    className={ 'var-control' }
    onClick={ () => !isOpen && toggleOpen()}
    style={ {
      // userSelect: 'none',
      position: 'relative',
      listStyleType: 'none',
      fontSize: '15px',
      clear: 'both',
      cursor: isOpen ? 'auto' : 'pointer',
    } }
  >
    { !!onCloseClick && <IconButton
      icon={ 'minus' }
      style={ { float: 'right', height: '29px' } }
      onClick={ () => onCloseClick(cssVar) }
    /> }
    { previewValue(value, cssVar, toggleOpen, isDefault) }
    <h5
      style={ {  fontSize: '16px', padding: '2px 4px 0', fontWeight: '400', userSelect: 'none', cursor: 'pointer' } }
      onClick={ ()=> isOpen && toggleOpen() }
    >
      { formatTitle(cssVar, isRepeat) }
    </h5>
    { isOpen && (
      <div
        onMouseEnter={()=> {
          if (PSEUDO_REGEX.test(cssVar.name)) {
            dispatch({type: THEME_ACTIONS.START_PREVIEW_PSEUDO_STATE, payload: {name: cssVar.name}});
          }
        }}
        onMouseLeave={()=> {
          if (PSEUDO_REGEX.test(cssVar.name)) {
            dispatch({type: THEME_ACTIONS.END_PREVIEW_PSEUDO_STATE, payload: {name: cssVar.name}})
          }
        }}
      >
        <div>{cssVar.name}</div>
        { showUsages(cssVar, showSelectors,toggleSelectors) }
        { isDefault && <span style={{float: 'right', marginBottom: '14.5px', color: 'grey'}}>default</span>}
        { !isDefault && <button
          style={ { float: 'right', marginBottom: '14.5px' } }
          title={`Reset to "${defaultValue}"`}
          onClick={ () => {
            onUnset(compoRef);
          } }
        >unset</button>}
        <TypedControl { ...{
          cssVar, theme, value, compoRef,dispatch, onChange: (value, updateRef) => {
            onChange(value, !updateRef ? null : compoRef);
          },
        } }/>
      </div>
    ) }
  </li>;
};
