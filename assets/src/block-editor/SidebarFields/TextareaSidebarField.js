const {TextareaControl} = wp.components;

export const TextareaSidebarField = ({value, setValue, label}) => (
  <TextareaControl
    label={label}
    value={value}
    onChange={setValue}
  />
);
