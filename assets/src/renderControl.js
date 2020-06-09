import { ColorPicker, TextControl, FontSizePicker, RangeControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import FontPicker from 'font-picker-react';
import { readProperty, STORAGE_KEY } from './VarPicker';

const googleApiKey = 'AIzaSyBt0d8TsNo0wJn8Pj2zICtBY614IsEqrHw';

export const COLOR_VALUE_REGEX = /(#[\da-fA-F]{3}|rgba?\()/;

const COLOR_REGEXP = /color$/;

const convertRemToPixels = ( rem ) => rem * parseFloat( getComputedStyle( document.documentElement ).fontSize );
const convertPixelsToRem = ( px ) =>  px / parseFloat( getComputedStyle( document.documentElement ).fontSize ) ;

const isPx = value => !!value && !!value.match( /[\d.]+px$/ );
const isRem = value => !!value && !!value.match( /[\d.]+rem$/ );

export const renderControl = ( { cssVar, value, onChange } ) => {

  if ( cssVar.usages.some( usage =>
    !! usage.property.match( COLOR_REGEXP )
    || [ 'background' ].includes( usage.property )
  ) ) {
    let currentTheme;
    try {
      currentTheme = JSON.parse(window.localStorage.getItem(STORAGE_KEY));
    } catch (e) {
      console.log(e);
    }

    const colorUsages = !currentTheme ? [] : Object.keys(currentTheme).reduce((colorUsages, name) => {
      const color = currentTheme[name];

      if (COLOR_VALUE_REGEX.test(color)) {
        const alreadyUsed = colorUsages.find(colorUsage=>colorUsage.color === color)

        if (!alreadyUsed) {
          colorUsages.push({ color, usages: [name] });
        } else {
          alreadyUsed.usages.push(name);
        }

      }

      return colorUsages;
    }, []);

    const previewSize = '30px';

    const byCountUsagesDesc = ({ usages: usages1 }, { usages: usages2 }) => usages2.length - usages1.length;

    return <Fragment>
      { colorUsages.sort(byCountUsagesDesc).map(({ color, usages }) => (<span
        onClick={ () => onChange(color) }
        title={ usages.join('\n') }
        style={ {
          width: previewSize,
          height: previewSize,
          border: color === value ? '3px solid yellow' :'1px solid black',
          borderRadius: '6px',
          backgroundColor: color,
          float: 'left',
          marginTop: '2px',
          fontSize: '8px',
        } }>
        <span style={{backgroundColor: 'white'}}>{ usages.length }</span>
        </span>)
      ) }
      <ColorPicker
        color={ readProperty(cssVar.name) || value }
        onChangeComplete={ color => {
          const hasTransparency = color.rgb.a !== 1;

          const { r, g, b, a } = color.rgb;

          onChange(hasTransparency ? `rgba(${ r } , ${ g }, ${ b }, ${ a })` : color.hex);
        } }
    />
      <TextControl
        value={ value }
        onChange={ onChange }
      />
    </Fragment>
  }

  if ( cssVar.usages.some( usage => [ 'font-size', 'border', 'border-bottom', 'line-height' ].includes( usage.property ) ) ) {
    return <div>
        <div key={1}>
          <span>px</span>
          <FontSizePicker
            value={ isPx( value ) ? value.replace('px', '') : convertRemToPixels( parseFloat( value.replace( 'rem', '' ) ) ) }
            onChange={ value => {
              onChange( `${ value }px` );
            } }
            style={{minWidth: '100px'}}
          />
        </div>
        <div key={2}>
          <span>rem</span>
          <input
            type={'number'}
            value={ isRem( value ) ? value.replace('rem', '') : convertPixelsToRem( parseFloat( value.replace( 'px', '' ) ) ) }
            onChange={ event => {
              onChange(`${event.currentTarget.value}rem`);
            } }
            style={{minWidth: '100px'}}
          />
        </div>
      <TextControl
        value={ value }
        onChange={ onChange }
      />
      </div>
  }

  if ( cssVar.usages.some( usage => usage.property === 'font-family' ) ) {
    return <Fragment>
      <FontPicker
        apiKey={ googleApiKey }
        activeFontFamily={ value }
        onChange={ value => onChange( value.family ) }
      />
      <TextControl
        value={ value }
        onChange={ onChange }
      />
    </Fragment>
  }

  // if ( cssVar.usages.some( usage => usage.property === 'font-weight' ) ) {
  // }

  return <Fragment>
    { !isNaN( value ) && <input type={ 'number' } onChange={ onChange } value={ value }/> }
    <TextControl
      value={ value }
      onChange={ onChange }
    />
  </Fragment>
};
