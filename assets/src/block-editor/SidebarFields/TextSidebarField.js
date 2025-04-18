const {TextControl} = wp.components;

export const TextSidebarField = ({value, setValue, label}) => (
  <TextControl
    __nextHasNoMarginBottom
    __next40pxDefaultSize
    label={label}
    value={value}
    onChange={setValue}
  />
);
