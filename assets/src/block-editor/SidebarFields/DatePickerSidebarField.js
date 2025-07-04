const {DatePicker, BaseControl} = wp.components;

export const DatePickerSidebarField = ({id, value, setValue, label, forceEndDate = false}) => (
  <BaseControl id={id} label={label}>
    <DatePicker
      id={id}
      currentDate={value}
      onChange={date => {
        if(date === value) {
          setValue(null);
          return;
        }

        setValue(forceEndDate ? `${date.slice(0,10)+'T23:59:59'}` : date);
      }}
      __nextHasNoMarginBottom
    />
  </BaseControl>
);
