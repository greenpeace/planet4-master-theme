import { useState, useRef } from 'react';
import { Fragment} from '@wordpress/element';
import { COLOR_VALUE_REGEX, TypedControl } from './typedControl';
import { IconButton } from '@wordpress/components';
import { readProperty } from './VarPicker';

const uniqueUsages = cssVar => {
  return [
    ...new Set(
      cssVar.usages.map(
        usage => usage.selector.replace(',', ',\n')
      )
    )
  ];
};

const uniqueProperties = cssVar => [...new Set(cssVar.usages.map(usage => usage.property))];

const capitalize = string => string.charAt(0).toUpperCase() + string.slice(1);
const format = name => name.replace(/^--/, '').replace(/--/g, ': ').replace(/[-_]/g, ' ');
const formatTitle = (cssVar) => capitalize(format(cssVar.name));

const previewValue = (value, cssVar, onClick) => {
  const size = '24px';

  if (value && value.match(COLOR_VALUE_REGEX)) {
    return <span
      key={ 1 }
      onClick={ onClick }
      title={ value }
      style={ {
        width: size,
        height: size,
        border: '1px solid black',
        borderRadius: '6px',
        backgroundColor: value,
        float: 'right',
        marginTop: '2px'
      } }/>;
  }
  return <span
    key={ 1 }
    onClick={ onClick }
    title={ value }
    style={ {
      // width: size,
      fontSize: '9px',
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
    changingVars,
    cssVar,
    onCloseClick,
    onChange,
    onUnset,
    defaultValue,
  } = props;

  const value = changingVars[cssVar.name]
    || readProperty(cssVar.name)
    || defaultValue;

  const [isOpen, setIsOpen] = useState(false);
  const toggleOpen = () => setIsOpen(!isOpen);

  const [showSelectors, setShowSelectors] = useState(false);
  const toggleSelectors = () => setShowSelectors(!showSelectors)

  const compoRef = useRef();

  return <li
    key={ cssVar.name }
    className={ 'var-control' }
    style={ {
      userSelect: 'none',
      position: 'relative',
      listStyleType: 'none',
      fontSize: '15px',
      clear: 'both',
    } }
  >
    { !!onCloseClick && <IconButton
      icon={ 'minus' }
      style={ { float: 'right', height: '29px' } }
      onClick={ () => onCloseClick(cssVar) }
    /> }
    { previewValue(value, cssVar, toggleOpen) }
    <h5
      style={ {  fontSize: '16px', padding: '2px 4px 0', userSelect: 'text', fontWeight: '400' } }
      onClick={ toggleOpen }
    >
      { formatTitle(cssVar) }
    </h5>
    { isOpen && (
      <Fragment>
        { showUsages(cssVar, showSelectors,toggleSelectors) }
        { value !== defaultValue && (
          <button
            style={ { float: 'right' } }
            onClick={ () => onUnset(compoRef) }
          > Unset </button>
        ) }
        <TypedControl { ...{
          cssVar, value, onChange: (value, updateRef) => {
            onChange(value, !updateRef ? null : compoRef);
          }, compoRef
        } }/>
        <pre
          style={ { float: 'right', fontSize: '11px', paddingLeft: '8px', backgroundColor: '#eae896' } }
        >
              { uniqueProperties(cssVar).join(', ') }
          </pre>
      </Fragment>
    ) }
  </li>;
};
