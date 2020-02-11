import { isEmpty } from 'lodash';
import classnames from 'classnames';
import { withInstanceId } from '@wordpress/compose';
import { BaseControl } from '@wordpress/components';
import { ColorPalette } from '@wordpress/components';

function ColorPaletteControl( { label, className, value, help, instanceId, onChange, options = [], ...passThroughProps } ) {
  const id = `inspector-color-palette-control-${ instanceId }`;

  return !isEmpty( options ) && (
    <BaseControl label={ label } id={ id } help={ help }
                 className={ classnames( className, 'components-color-palette-control' ) }>
      <ColorPalette
        value={ value }
        onChange={ onChange }
        colors={ options }
        { ...passThroughProps }
      />
    </BaseControl>
  );
}

export default withInstanceId( ColorPaletteControl );
