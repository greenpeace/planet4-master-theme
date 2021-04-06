import {
  Button,
} from '@wordpress/components';
const { __ } = wp.i18n;

export const EditorControls = ({ slides, setAttributes, currentSlide, goToSlide }) => {

  const addSlide = () => {
    const newSlides = slides.concat({
      image: null,
      focal_points: {},
      header: '',
      header_size: 'h1',
      description: '',
      link_text: '',
      link_url: '',
      link_url_new_tab: false,
    });
    setAttributes({ slides: newSlides });
  };

  const removeSlide = () => {
    const newSlides = [
      ...slides.slice(0, currentSlide),
      ...slides.slice(currentSlide + 1)
    ];
    const lastSlide = newSlides.length - 1;
    setAttributes({ slides: newSlides });
    goToSlide(currentSlide > lastSlide ? 0 : currentSlide, true);
  }

  return (
    <div className='carousel-header-add-remove-slide'>
      <Button
        isSecondary
        onClick={removeSlide}
        disabled={slides.length <= 1}
      >
        {__('Remove slide', 'planet4-blocks-backend')}
      </Button>
      <Button
        isPrimary
        onClick={addSlide}
        disabled={slides.length >= 4}
      >
        {__('Add slide', 'planet4-blocks-backend')}
      </Button>
    </div>
  );
}
