const {RadioControl} = wp.components;
const {__} = wp.i18n;

const NAVIGATION_TYPE_PLANET4 = 'planet4';
const NAVIGATION_TYPE_MINIMAL = 'minimal';

// Navigation type selector
export const NavigationType = ({value, setValue, defaultValue, options}) => {
  const defaultOptions = [
    {
      label: __('Main website navigation', 'planet4-master-theme-backend'),
      value: NAVIGATION_TYPE_PLANET4,
    },
    {
      label: __('Minimal Navigation', 'planet4-master-theme-backend'),
      value: NAVIGATION_TYPE_MINIMAL,
    },
  ];

  return (
    <RadioControl
      label={__('Navigation type', 'planet4-master-theme-backend')}
      selected={value || defaultValue || NAVIGATION_TYPE_PLANET4}
      options={options || defaultOptions}
      onChange={setValue}
    />
  );
};
