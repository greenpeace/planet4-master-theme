import { useRef } from '@wordpress/element';

// Carousel Header
import { Slide } from './Slide';
import { Caption } from './Caption';
import { useSlides } from './useSlides';
import { CarouselControls } from './CarouselControls';

// Carousel Header Editor
import { Sidebar } from './Sidebar';
import { EditableBackground } from './EditableBackground';
import { useCarouselHeaderImages } from './useCarouselHeaderImages';

export const CarouselHeaderEditor = ({ setAttributes, attributes }) => {
  const { carousel_autoplay, slides, className } = attributes;
  const slidesRef = useRef([]);
  const slidesWithImages = useCarouselHeaderImages(slides);

  const { currentSlide, goToSlide, goToNextSlide, goToPrevSlide } = useSlides(slidesRef, slides.length - 1);

  const changeSlideAttribute = (slideAttributeName, index) => value => {
    const newSlides = JSON.parse(JSON.stringify(slides));
    newSlides[index][slideAttributeName] = value;
    setAttributes({ slides: newSlides });
  }

  const addSlide = () => {
    const newSlides = slides.concat({
      image: null,
      focal_points: {},
      header: '',
      description: '',
      link_text: '',
      link_url: '',
      link_url_new_tab: false,
    });
    setAttributes({ slides: newSlides });
    const lastSlide = newSlides.length - 1;
    // There is no callback to setAttributes so we use timeout instead
    setTimeout(() => goToSlide(lastSlide), 0);
  }

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
    <section className={`block block-header block-wide carousel-header-beta ${className ?? ''}`}>
      <Sidebar
        carouselAutoplay={carousel_autoplay}
        slides={slidesWithImages}
        setAttributes={setAttributes}
        currentSlide={currentSlide}
        changeSlideAttribute={changeSlideAttribute}
        goToSlide={goToSlide}
      />
      <div className='carousel-wrapper-header'>
        <div className='carousel-inner' role='listbox'>
          {slidesWithImages?.map((slide, index) => (
            <Slide
              key={index}
              ref={element => slidesRef.current[index] = element}
              active={currentSlide === index}
            >
              <EditableBackground
                image_url={slide.image_url}
                focalPoints={slide.focal_points}
                image_id={slide.image}
                index={index}
                changeSlideAttribute={changeSlideAttribute}
                addSlide={addSlide}
                removeSlide={removeSlide}
                slides={slides}
              />
              <Caption
                slide={slide}
                index={index}
                changeSlideAttribute={changeSlideAttribute}
              />
            </Slide>
          ))}
          <CarouselControls
            goToPrevSlide={goToPrevSlide}
            goToNextSlide={goToNextSlide}
            goToSlide={goToSlide}
            slides={slides}
            currentSlide={currentSlide}
          />
        </div>
      </div>
    </section>
  );
}
