import {SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';

const AnimationSelectControl = ({
  animation,
  onChangeAnimation = () => {},
}) => {
  return (
    <SelectControl
      label={__('Select Animation', 'planet4-blocks-backend')}
      value={animation}
      options={[
        {label: __('None', 'planet4-blocks-backend'), value: ''},
        {label: __('Slide In Up', 'planet4-blocks-backend'), value: 'animate__slideInUp'},
        {label: __('Slide In Down', 'planet4-blocks-backend'), value: 'animate__slideInDown'},
        {label: __('Slide In Left', 'planet4-blocks-backend'), value: 'animate__slideInLeft'},
        {label: __('Slide In Right', 'planet4-blocks-backend'), value: 'animate__slideInRight'},
      ]}
      onChange={onChangeAnimation}
    />
  );
};

export default AnimationSelectControl;

{/* <SelectControl
label={__('Animation Delay', 'planet4-blocks-backend')}
value={animationDelay}
options={[
  {label: __('0 seconds', 'planet4-blocks-backend'), value: ''},
  {label: __('2 seconds', 'planet4-blocks-backend'), value: 'animate__delay-2s'},
  {label: __('3 seconds', 'planet4-blocks-backend'), value: 'animate__delay-3s'},
  {label: __('4 seconds', 'planet4-blocks-backend'), value: 'animate__delay-4s'},
  {label: __('5 seconds', 'planet4-blocks-backend'), value: 'animate__delay-5s'},
]}
onChange={onChangeAnimationDelay}
/>
<SelectControl
label={__('Animation Speed', 'planet4-blocks-backend')}
value={animationSpeed}
options={[
  {label: __('Normal', 'planet4-blocks-backend'), value: ''},
  {label: __('Slow (2s)', 'planet4-blocks-backend'), value: 'animate__slow'},
  {label: __('Slower (3s)', 'planet4-blocks-backend'), value: 'animate__slower'},
  {label: __('Fast (800ms)', 'planet4-blocks-backend'), value: 'animate__fast'},
  {label: __('Faster (500ms)', 'planet4-blocks-backend'), value: 'animate__faster'},
]}
onChange={onChangeAnimationSpeed}
/>
<SelectControl
label={__('Repeat Animation', 'planet4-blocks-backend')}
value={animationRepeat}
options={[
  {label: __('No repeat', 'planet4-blocks-backend'), value: ''},
  {label: __('Repeat 1 time', 'planet4-blocks-backend'), value: 'animate__repeat-1'},
  {label: __('Repeat 2 times', 'planet4-blocks-backend'), value: 'animate__repeat-2'},
  {label: __('Repeat 3 times', 'planet4-blocks-backend'), value: 'animate__repeat-3'},
  {label: __('Infinite repeat', 'planet4-blocks-backend'), value: 'animate__infinite'},
]}
onChange={onChangeAnimationRepeat}
/> */}
