import tinycolor from 'tinycolor2';

export function colorToState( data = {}, oldHue = false ) {
  const color = data.hex ? tinycolor( data.hex ) : tinycolor( data );
  const hsl = color.toHsl();
  hsl.h = Math.round( hsl.h );
  hsl.s = Math.round( hsl.s * 100 );
  hsl.l = Math.round( hsl.l * 100 );
  const hsv = color.toHsv();
  hsv.h = Math.round( hsv.h );
  hsv.s = Math.round( hsv.s * 100 );
  hsv.v = Math.round( hsv.v * 100 );
  const rgb = color.toRgb();
  const hex = color.toHex();
  if ( hsl.s === 0 ) {
    hsl.h = oldHue || 0;
    hsv.h = oldHue || 0;
  }
  const transparent = hex === '000000' && rgb.a === 0;

  return {
    color,
    hex: transparent ? 'transparent' : `#${ hex }`,
    hsl,
    hsv,
    oldHue: data.h || oldHue || hsl.h,
    rgb,
    source: data.source,
  };
}
