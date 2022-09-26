import { TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const TextareaSidebarField = ({ value, setValue, label }) => (
  <TextareaControl
    label={label}
    value={value}
    onChange={setValue}
  />
);
