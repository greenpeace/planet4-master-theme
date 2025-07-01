const {DatePicker, BaseControl} = wp.components;

export const DatePickerSidebarField = ({id, value, setValue, label, forceEndDate = false}) => (
  <BaseControl id={id} label={label}>
    <DatePicker
      id={id}
      currentDate={value}
      onChange={date => {
        if(forceEndDate) {
          setValue(`${date.slice(0,10)+'T23:59:59'}`);
        } else {
          setValue(date);
        }
      }}
      __nextHasNoMarginBottom
    />
  </BaseControl>
);
