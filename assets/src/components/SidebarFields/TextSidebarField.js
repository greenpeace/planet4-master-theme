import {TextControl} from '@wordpress/components';

export const TextSidebarField = ({value, setValue, label}) => (
  <TextControl
    label={label}
    value={value}
    onChange={setValue}
  />
);
