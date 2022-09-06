import { CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Hide page title sidebar setting.
 */
export const HidePageTitle = ({ value, setValue }) => (
  <CheckboxControl
    label={__( 'Hide page title', 'planet4-blocks-backend' )}
    checked={value === 'on'}
    value={value === 'on'}
    onChange={checked => setValue(checked ? 'on' : '')}
  />
);
