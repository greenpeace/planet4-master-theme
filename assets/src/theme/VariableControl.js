import { useState, Fragment } from 'react';
import { COLOR_VALUE_REGEX, TypedControl } from './typedControl';
import { Button } from '@wordpress/components';
import { PSEUDO_REGEX, THEME_ACTIONS} from './useThemeEditor';
import classnames from 'classnames';

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

const GRADIENT_REGEX = /linear-gradient\(.+\)/;
const previewValue = (value, cssVar, onClick, isDefault) => {
  const size = '30px';

  const title = `${value}${ !isDefault ? '' : ' (default)' }`;

  if (value && `${value}`.match(COLOR_VALUE_REGEX) || `${value}`.match(GRADIENT_REGEX)) {
    return <span
      key={ 1 }
      onClick={ onClick }
      title={ title }
      style={ {
        width: size,
        height: size,
        border: '1px solid black',
        borderRadius: '6px',
        background: value,
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
const renderCollapsed = ({cssVar, toggleSelectors}) => <pre
  // style={ { maxWidth: '80%', overflowX: 'hidden' } }
  onClick={ toggleSelectors }
  className={ 'usages-collapsed' }
>
    { uniqueUsages(cssVar).join(', ') }
  </pre>;

const renderShow = ({cssVar, toggleSelectors}) => <pre
  onClick={ toggleSelectors }
>
    { uniqueUsages(cssVar).join('\n').replace(',', ',\n') }
  </pre>;

const showUsages = (cssVar, showSelectors, toggleSelectors) => {

  return <div
    style={ { display: 'inline-block', fontSize: '11px', position: 'relative', marginTop: '16px', minWidth: '40%' } }
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
    { showSelectors
      ? renderShow({ cssVar, toggleSelectors })
      : renderCollapsed({ cssVar, toggleSelectors }) }

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
  ] = useState(true);

  const toggleSelectors = () => setShowSelectors(!showSelectors)
  const value = theme[cssVar.name] || defaultValue;
  const isDefault = value === defaultValue;

  return <li
    key={ cssVar.name }
    className={ classnames('var-control', {'var-control-in-theme': cssVar.name in theme }) }
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
    { !!onCloseClick && <Button
      style={ {
        float: 'right',
        borderRadius: '7px',
        marginTop: '9px',
        marginLeft: '9px',
        padding: '3px 10px',
        height: '29px',
        fontSize: '32px',
        border: '1px solid black'
      } }
      title='Close'
      onClick={ () => onCloseClick(cssVar) }>-</Button> }
    { previewValue(value, cssVar, toggleOpen, isDefault) }
    <h5
      style={ {  fontSize: '16px', padding: '2px 4px 0', fontWeight: '400', userSelect: 'none', cursor: 'pointer' } }
      onClick={ ()=> isOpen && toggleOpen() }
    >
      { formatTitle(cssVar, isRepeat) }
    </h5>
    { isOpen && (
      <div
        onMouseEnter={ () => {
          PSEUDO_REGEX.test(cssVar.name) && dispatch({
            type: THEME_ACTIONS.START_PREVIEW_PSEUDO_STATE,
            payload: { name: cssVar.name }
          });
        } }
        onMouseLeave={ () => {
          PSEUDO_REGEX.test(cssVar.name) && dispatch({
            type: THEME_ACTIONS.END_PREVIEW_PSEUDO_STATE,
            payload: { name: cssVar.name }
          });
        } }
      >
        <div>{cssVar.name}</div>
        { showUsages(cssVar, showSelectors,toggleSelectors) }
        { isDefault && <span style={{float: 'right', marginBottom: '14.5px', color: 'grey'}}>default</span>}
        { !isDefault && <button
          style={ { float: 'right', marginBottom: '14.5px' } }
          title={`Reset to "${defaultValue}"`}
          onClick={ () => {
            onUnset();
          } }
        >unset</button>}
        <TypedControl { ...{
          cssVar, theme, value, dispatch, onChange,
        } }/>
      </div>
    ) }
  </li>;
};
