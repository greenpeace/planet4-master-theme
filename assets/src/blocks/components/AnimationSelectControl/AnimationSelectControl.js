import {SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';

const AnimationSelectControl = ({animation, onChange}) => {
  return (
    <SelectControl
      label={__('Animations', 'planet4-blocks-backend')}
      value={animation}
      options={[
        {label: __('None', 'planet4-blocks-backend'), value: ''},
        {label: __('Slide In Up', 'planet4-blocks-backend'), value: 'animate__slideInUp'},
        {label: __('Slide In Down', 'planet4-blocks-backend'), value: 'animate__slideInDown'},
        {label: __('Slide In Left', 'planet4-blocks-backend'), value: 'animate__slideInLeft'},
        {label: __('Slide In Right', 'planet4-blocks-backend'), value: 'animate__slideInRight'},
      ]}
      onChange={onChange}
    />
  );
};

export default AnimationSelectControl;
