const {DatePicker, BaseControl} = wp.components;

export const DatePickerSidebarField = ({id, value, setValue, label, forceEndDate = false}) => (
  <BaseControl id={id} label={label}>
    <DatePicker
      id={id}
      currentDate={value}
      onChange={date => {
<<<<<<< HEAD
        if(date === value) {
          setValue(null);
          return;
        }

        setValue(forceEndDate ? `${date.slice(0,10)+'T23:59:59'}` : date);
=======
        if(forceEndDate) {
          setValue(`${date.slice(0,10)+'T23:59:59'}`);
        } else {
          setValue(date);
        }
>>>>>>> 653be1ef (Ref: https://jira.greenpeace.org/browse/PLANET-7828)
      }}
      __nextHasNoMarginBottom
    />
  </BaseControl>
<<<<<<< HEAD
);
=======
);
>>>>>>> 653be1ef (Ref: https://jira.greenpeace.org/browse/PLANET-7828)
