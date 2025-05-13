const {SelectControl} = wp.components;

export const SelectSidebarField = ({options, value, setValue, label}) => (
  <SelectControl
    __nextHasNoMarginBottom
    __next40pxDefaultSize
    label={label}
    options={options}
    value={value}
    onChange={setValue}
  />
);
