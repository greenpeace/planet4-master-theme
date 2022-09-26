import { CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const CheckboxSidebarField = ({ value, setValue, label }) => (
  <CheckboxControl
    label={label}
    checked={value === 'on'}
    value={value === 'on'}
    onChange={checked => setValue(checked ? 'on' : '')}
  />
);
