import {TextareaControl} from '@wordpress/components';

export const TextareaSidebarField = ({value, setValue, label}) => (
  <TextareaControl
    label={label}
    value={value}
    onChange={setValue}
  />
);
