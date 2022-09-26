import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const TextSidebarField = ({ value, setValue, label }) => (
  <TextControl
    label={label}
    value={value}
    onChange={setValue}
  />
);
