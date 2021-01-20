import {
  Button,
} from '@wordpress/components';

export const EditorControls = ({ slides, addSlide, removeSlide }) => {
  return  <div className='carousel-header-add-remove-slide'>
    <Button isSecondary
      onClick={removeSlide}
      disabled={slides.length <= 1}>
      Remove Slide
    </Button>
    <Button isPrimary
      onClick={ addSlide }
      disabled={slides.length >= 4}>
      Add Slide
    </Button>
  </div>
};
