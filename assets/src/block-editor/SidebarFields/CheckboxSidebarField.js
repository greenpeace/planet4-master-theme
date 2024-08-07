const {CheckboxControl} = wp.components;

export const CheckboxSidebarField = ({value, setValue, label}) => (
  <CheckboxControl
    label={label}
    checked={value === 'on'}
    value={value === 'on'}
    onChange={checked => setValue(checked ? 'on' : '')}
  />
);
