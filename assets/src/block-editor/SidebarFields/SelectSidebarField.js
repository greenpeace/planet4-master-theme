const {SelectControl} = wp.components;

export const SelectSidebarField = ({options, value, setValue, label, id}) => (
  <>
    <label htmlFor={id}>{label}</label>
    <SelectControl
      id={id}
      options={options}
      value={value}
      onChange={setValue}
    />
  </>
);
