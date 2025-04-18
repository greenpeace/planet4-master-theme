const {CheckboxControl} = wp.components;

export const CheckboxSidebarField = ({value, setValue, label}) => (
  <CheckboxControl
    __nextHasNoMarginBottom
    label={label}
    checked={value === 'on'}
    value={value === 'on'}
    onChange={checked => setValue(checked ? 'on' : '')}
  />
);
