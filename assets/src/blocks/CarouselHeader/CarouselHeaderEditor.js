import { useRef } from '@wordpress/element';

// Carousel Header
import { SlidesContainer } from './SlidesContainer';
import { Slide } from './Slide';
import { Caption } from './Caption';
import { useSlides } from './useSlides';

// Carousel Header Editor
import { Sidebar } from './Sidebar';
import { EditorControls } from './EditorControls';
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

  return (
    <>
      <Sidebar
        carouselAutoplay={carousel_autoplay}
        slides={slidesWithImages}
        setAttributes={setAttributes}
        currentSlide={currentSlide}
        changeSlideAttribute={changeSlideAttribute}
      />

      <SlidesContainer
        slides={slides}
        goToSlide={goToSlide}
        goToNextSlide={goToNextSlide}
        goToPrevSlide={goToPrevSlide}
      >
        {slidesWithImages?.map((slide, index) => (
          <Slide
            key={index}
            index={index}
            ref={element => slidesRef.current[index] = element}
            active={currentSlide == index}
          >
            <EditableBackground
              image_url={slide.image_url}
              focalPoints={slide.focal_points}
              image_id={slide.image}
              index={index}
              changeSlideAttribute={changeSlideAttribute}
            >
              <Caption
                slide={slide}
                index={index}
                changeSlideAttribute={changeSlideAttribute}
              />
            </EditableBackground>
          </Slide>
        ))}

        <EditorControls
          slides={slides}
          setAttributes={setAttributes}
          currentSlide={currentSlide}
          goToSlide={goToSlide}
        />
      </SlidesContainer>
    </>
  );
}
