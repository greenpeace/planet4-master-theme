import {  TextControl } from '@wordpress/components';
import { SketchPicker as ColorPicker} from 'react-color';
import tinycolor from 'tinycolor2';
import { Fragment } from 'react';
import FontPicker from 'font-picker-react';
import { THEME_ACTIONS} from './useThemeEditor';

const googleApiKey = 'AIzaSyBt0d8TsNo0wJn8Pj2zICtBY614IsEqrHw';

export const COLOR_VALUE_REGEX = /(#[\da-fA-F]{3}|rgba?\()/;

const convertRemToPixels = (rem) => rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
const convertPixelsToRem = (px) => px / parseFloat(getComputedStyle(document.documentElement).fontSize);

const isPx = value => value && value.match(/[\d.]+px$/);
const isRem = value => value && value.match(/[\d.]+rem$/);
const isPercent = value => value && value.match(/\d%$/);
const isVh = value => value && value.match(/vh$/);
const isVw = value => value && value.match(/vw$/);

const extractColorUsages = theme => {
  if (null === theme) {
    return [];
  }
  const keys = Object.keys(theme);

  return keys.reduce((colors = [] , name) => {
    const color = theme[name];

    if (COLOR_VALUE_REGEX.test(color)) {
      const alreadyUsed = colors.find(colorUsage => colorUsage.color === color);

      if (!alreadyUsed) {
        colors.push({ color, usages: [name] });
      } else {
        alreadyUsed.usages.push(name);
      }
    }

    return colors;
  }, []);
}

// const byCountUsagesDesc = ({ usages: usages1 }, { usages: usages2 }) => usages2.length - usages1.length;

const byHexValue = ({color1}, { color2}) => {
  const hex1 = tinycolor(color1).toHex();
  const hex2 = tinycolor(color2).toHex();

  if (hex1 === hex2) {
    return color1 < color2 ? 1 : -1;
  }

  return hex1 < hex2 ? 1 : -1;
}

export const TypedControl = ({ cssVar, theme, value, onChange, dispatch }) => {

  if (cssVar.usages.some(usage =>
    !!usage.property.match(/color$/)
    || ['background', 'fill'].includes(usage.property)
  )) {
    const colorUsages = extractColorUsages(theme);

    const PREVIEW_SIZE = '30px';

    return <Fragment>
      <ColorPicker
        styles={{
          picker: {
            width: 'calc(100%)',
          }
        }}
        color={ value }
        onChange={ color => {
          const hasTransparency = color.rgb.a !== 1;

          const { r, g, b, a } = color.rgb;

          onChange(hasTransparency ? `rgba(${ r } , ${ g }, ${ b }, ${ a })` : color.hex);
        } }
      />
      { colorUsages.sort(byHexValue).map(({ color, usages }) => (<span
        key={color}
        onClick={ () => {
          onChange(color, true);
        } }
        onMouseEnter={ () => {
          dispatch({ type: THEME_ACTIONS.START_PREVIEW, payload: { name: cssVar.name, value: color } });
        }}
        onMouseLeave={ () => {
          dispatch({ type: THEME_ACTIONS.END_PREVIEW, payload: { name: cssVar.name } });
        }}
        title={ `${ color }\nUsed for:\n` + usages.join('\n') }
        style={ {
          width: PREVIEW_SIZE,
          height: PREVIEW_SIZE,
          border: color === value ? '3px solid yellow' : '1px solid black',
          marginRight: '5px',
          marginBottom: '2px',
          borderRadius: '6px',
          background: color,
          display: 'inline-block',
          marginTop: '2px',
          fontSize: '8px',
          cursor: 'pointer',
        } }>
        <span key={`${color}---usages`} style={ { backgroundColor: 'white' } }>{ usages.length }</span>
        </span>)
      ) }
      <div>
        <TextControl style={{marginTop: '6px'}}
          value={ value }
          onChange={ value=>onChange(value, true) }
        />
      </div>
    </Fragment>;
  }

  const sizeLikeProperties = [
    'font-size',
    'border',
    'border-bottom',
    'border-bottom-width',
    'line-height',
    'border-radius',
    'margin',
    'margin-bottom',
    'margin-top',
    'margin-left',
    'margin-right',
    'padding',
    'padding-bottom',
    'padding-left',
    'padding-right',
    'padding-top',
    'width',
    'height',
    'min-width',
    'max-width',
    'min-height',
    'max-height',
    'letter-spacing',
    'outline-offset',
  ];

  if (cssVar.usages.some(usage => sizeLikeProperties.includes(usage.property))) {
    const pxValue = isPx(value) ? value.replace('px', '') : isRem(value) ? convertRemToPixels(parseFloat(value.replace('rem', ''))) : '';
    const remValue = isRem(value) ? value.replace('rem', '') : isPx(value) ? convertPixelsToRem(parseFloat(value.replace('px', ''))) : '';
    return <div className='theme-length-controls'>
      <div key={ 1 } style={{clear: 'both'}}>
        <input
          type={ 'number' }
          value={pxValue}
          onChange={ event => {
            onChange(`${ event.currentTarget.value }px`);
          } }
          style={ { minWidth: '100px' } }
        />
        <span>px</span>
      </div>
      <div key={ 2 }>
        <input
          type={ 'number' }
          value={remValue}
          onChange={ event => {
            onChange(`${ event.currentTarget.value }rem`);
          } }
          style={ { minWidth: '100px' } }
        />
        <span>rem</span>
      </div>
      <div key={ 3 }>
        <input
          type={ 'number' }
          value={ isPercent(value) ? value.replace('%', '') : ''}
          onChange={ event => {
            onChange(`${ event.currentTarget.value }%`);
          } }
          style={ { minWidth: '100px' } }
        />
        <span>%</span>
      </div>
      <div key={ 4 }>
        <input
          type={ 'number' }
          value={ isVh(value) ? value.replace('vh', '') : ''}
          onChange={ event => {
            onChange(`${ event.currentTarget.value }vh`);
          } }
          style={ { minWidth: '100px' } }
        />
        <span>vh</span>
      </div>
      <div key={ 5 }>
        <input
          type={ 'number' }
          value={ isVw(value) ? value.replace('vw', '') : ''}
          onChange={ event => {
            onChange(`${ event.currentTarget.value }vw`);
          } }
          style={ { minWidth: '100px' } }
        />
        <span>vw</span>
      </div>
      <TextControl
        value={ value }
        onChange={ onChange }
      />
    </div>;
  }

  if (cssVar.usages.some(usage => usage.property === 'font-family')) {
    return <Fragment>
      {/*<FontPicker*/}
      {/*  apiKey={ googleApiKey }*/}
      {/*  activeFontFamily={ value }*/}
      {/*  onChange={ value => onChange(value.family) }*/}
      {/*/>*/}
      <TextControl
        value={ value }
        onChange={ onChange }
      />
    </Fragment>;
  }

  // if ( cssVar.usages.some( usage => usage.property === 'font-weight' ) ) {
  // }

  return <Fragment>
    { !isNaN(value) && <input type={ 'number' } onChange={ e => onChange(e.target.value) } value={ value }/> }
    <TextControl
      value={ value }
      onChange={ onChange }
    />
  </Fragment>;
};
