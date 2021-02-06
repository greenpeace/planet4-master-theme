import { ColorPicker, TextControl, FontSizePicker} from '@wordpress/components';
import tinycolor from 'tinycolor2';
import { Fragment } from '@wordpress/element';
import FontPicker from 'font-picker-react';
import { THEME_ACTIONS} from './useThemeEditor';

const googleApiKey = 'AIzaSyBt0d8TsNo0wJn8Pj2zICtBY614IsEqrHw';

export const COLOR_VALUE_REGEX = /(#[\da-fA-F]{3}|rgba?\()/;

const convertRemToPixels = (rem) => rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
const convertPixelsToRem = (px) => px / parseFloat(getComputedStyle(document.documentElement).fontSize);

const isPx = value => !!value && !!value.match(/[\d.]+px$/);
const isRem = value => !!value && !!value.match(/[\d.]+rem$/);

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

const byCountUsagesDesc = ({ usages: usages1 }, { usages: usages2 }) => usages2.length - usages1.length;

const byHexValue = ({color1}, { color2}) => {
  const hex1 = tinycolor(color1).toHex();
  const hex2 = tinycolor(color2).toHex();

  if (hex1 === hex2) {
    return color1 < color2 ? 1 : -1;
  }

  return hex1 < hex2 ? 1 : -1;
}

export const TypedControl = ({ cssVar, theme, value, onChange, compoRef, dispatch }) => {

  if (cssVar.usages.some(usage =>
    !!usage.property.match(/color$/)
    || ['background'].includes(usage.property)
  )) {
    const colorUsages = extractColorUsages(theme);

    const PREVIEW_SIZE = '30px';

    return <Fragment>
      <ColorPicker
        ref={ compoRef }
        color={ value }
        onChangeComplete={ color => {
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
          // console.log('enter preview span')
          dispatch({ type: THEME_ACTIONS.START_PREVIEW, payload: { name: cssVar.name, value: color } });
        }}
        onMouseLeave={ () => {
          // console.log('exit preview span')
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
          backgroundColor: color,
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
    'padding',
    'padding-bottom',
    'padding-left',
    'padding-top',
    'height',
    'min-width',
    'letter-spacing',
  ];

  if (cssVar.usages.some(usage => sizeLikeProperties.includes(usage.property))) {
    return <div>
      <div key={ 1 }>
        <span>px</span>
        <FontSizePicker
          value={ isPx(value) ? value.replace('px', '') : convertRemToPixels(parseFloat(value.replace('rem', ''))) }
          onChange={ value => {
            onChange(`${ value }px`);
          } }
          style={ { minWidth: '100px' } }
        />
      </div>
      <div key={ 2 }>
        <span>rem</span>
        <input
          type={ 'number' }
          value={ isRem(value) ? value.replace('rem', '') : convertPixelsToRem(parseFloat(value.replace('px', ''))) }
          onChange={ event => {
            onChange(`${ event.currentTarget.value }rem`);
          } }
          style={ { minWidth: '100px' } }
        />
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
