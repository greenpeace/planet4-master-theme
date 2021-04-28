import { useRef } from '@wordpress/element';

// Carousel Header
import { SlidesContainer } from './SlidesContainer';
import { Slide } from './Slide';
import { Caption } from './Caption';
import { useSlides } from './useSlides';

// Carousel Header Editor
import { Sidebar } from './Sidebar';
import { EditableBackground } from './EditableBackground';
import { useCarouselHeaderImages } from './useCarouselHeaderImages';

export const CarouselHeaderEditor = ({ setAttributes, attributes }) => {
  const { carousel_autoplay, slides } = attributes;
  const slidesRef = useRef([]);
  const slidesWithImages = useCarouselHeaderImages(slides);

  const { currentSlide, goToSlide, goToNextSlide, goToPrevSlide } = useSlides(slidesRef, slides.length);

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
    <>
      <Sidebar
        carouselAutoplay={carousel_autoplay}
        slides={slidesWithImages}
        setAttributes={setAttributes}
        currentSlide={currentSlide}
        changeSlideAttribute={changeSlideAttribute}
        goToSlide={goToSlide}
      />

      <SlidesContainer
        slides={slides}
        goToSlide={goToSlide}
        goToNextSlide={goToNextSlide}
        goToPrevSlide={goToPrevSlide}
        currentSlide={currentSlide}
      >
        {slidesWithImages?.map((slide, index) => (
          <Slide
            key={index}
            index={index}
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
      </SlidesContainer>
    </>
  );
}
