const {SelectControl} = wp.components;

export const SelectSidebarField = ({options, value, setValue, label}) => (
  <SelectControl
    label={label}
    options={options}
    value={value}
    onChange={setValue}
  />
);
