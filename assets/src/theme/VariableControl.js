import { useState, useRef} from 'react';
import { Fragment} from '@wordpress/element';
import { COLOR_VALUE_REGEX, TypedControl } from './typedControl';
import { IconButton } from '@wordpress/components';
import { readProperty } from './VarPicker';
import { usePreview } from './usePreview';

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
const format = name => {
  const raw = name.replace(/^--/, '').replace(/--/g, ': ').replace(/[-_]/g, ' ')
  const parts = raw.split(':');

  return [parts.slice(0, parts.length - 1).join(':') + ':', parts[parts.length - 1]];
};
const formatTitle = (cssVar, isRepeat) => {
  const [prefix, prop] = format(cssVar.name);
  return <Fragment>
    <span style={ { fontWeight: 'bold', color: isRepeat ? 'grey' : 'black' } }>{capitalize(prefix)}</span>
    <span>{prop}</span>
  </Fragment>;
};

const previewValue = (value, cssVar, onClick, isDefault) => {
  const size = '24px';

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
        marginTop: '2px'
      } }/>;
  }

  return <span
    key={ 1 }
    onClick={ onClick }
    title={ title }
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
    isRepeat = false,
    refresh,
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

  const varValue = changingVars[cssVar.name]
    || readProperty(cssVar.name)
    || defaultValue;

  const {
    setIsPreviewing: setPreviewing,
    setValue: setPreviewValue,
    isPreviewing,
    origValue: actualValue,
    setOrigValue: updatePreviewOriginal,
  } = usePreview(cssVar.name, varValue);

  const value = isPreviewing ? (actualValue || varValue) : varValue;

  const isDefault = value === defaultValue;

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
    { previewValue(value, cssVar, toggleOpen, isDefault) }
    <h5
      style={ {  fontSize: '16px', padding: '2px 4px 0', userSelect: 'text', fontWeight: '400' } }
      onClick={ toggleOpen }
    >
      { formatTitle(cssVar, isRepeat) }
    </h5>
    { isOpen && (
      <div
        // onMouseEnter={()=> {
        //   setPreviewing(true);
        // }}
        // onMouseLeave={()=> {
        //   setPreviewing(false);
        // }}
      >
        <div>{cssVar.name}</div>
        { showUsages(cssVar, showSelectors,toggleSelectors) }
        { isDefault && <span style={{float: 'right', marginBottom: '14.5px', color: 'grey'}}>default</span>}
        { !isDefault && <button
          style={ { float: 'right', marginBottom: '14.5px' } }
          title={`Reset to "${defaultValue}"`}
          onClick={ () => {
            setPreviewValue(defaultValue);
            onUnset(compoRef);
            updatePreviewOriginal(null)
          } }
        >unset</button>}
        <TypedControl { ...{
          cssVar, value,compoRef,refresh, setPreviewColor: setPreviewValue,updatePreviewOriginal, onChange: (value, updateRef) => {
            setPreviewValue(value);
            updatePreviewOriginal(value);
            onChange(value, !updateRef ? null : compoRef);
          },
        } }/>
        <pre
          style={ { float: 'right', fontSize: '11px', paddingLeft: '8px', backgroundColor: '#eae896' } }
        >
              { uniqueProperties(cssVar).join(', ') }
          </pre>
      </div>
    ) }
  </li>;
};
