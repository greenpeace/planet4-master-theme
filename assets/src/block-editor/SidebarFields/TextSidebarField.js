const {TextControl} = wp.components;

export const TextSidebarField = ({value, setValue, label}) => (
  <TextControl
    label={label}
    value={value}
    onChange={setValue}
  />
);
