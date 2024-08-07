import classnames from 'classnames';
import {BaseControl, ColorPalette} from '@wordpress/components';

const {withInstanceId} = wp.compose;

function ColorPaletteControl({label, className, value, help, instanceId, onChange, options = [], ...passThroughProps}) {
  const id = `inspector-color-palette-control-${instanceId}`;

  // eslint-disable-next-line no-shadow
  const optionsAsColors = options.map(({value, ...props}) => ({color: value, ...props}));

  return options.length > 0 && (
    <BaseControl label={label} id={id} help={help}
      className={classnames(className, 'components-color-palette-control')}>
      <ColorPalette
        value={value}
        onChange={onChange}
        colors={optionsAsColors}
        {...passThroughProps}
      />
    </BaseControl>
  );
}

export default withInstanceId(ColorPaletteControl);
