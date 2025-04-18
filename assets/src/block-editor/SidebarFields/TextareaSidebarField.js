const {TextareaControl} = wp.components;

export const TextareaSidebarField = ({value, setValue, label}) => (
  <TextareaControl
    __nextHasNoMarginBottom
    label={label}
    value={value}
    onChange={setValue}
  />
);
