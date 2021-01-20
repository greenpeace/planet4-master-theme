import { useRef, useEffect } from '@wordpress/element';

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
import { addSlide } from './addSlide';
import { removeSlide } from './removeSlide';

export function CarouselHeaderEditor({ setAttributes, attributes, isSelected }) {
  const { carousel_autoplay, slides } = attributes;
  const slidesRef = useRef([]);
  const slidesWithImages = useCarouselHeaderImages(slides);

  const { currentSlide, goToSlide, goToNextSlide, goToPrevSlide } = useSlides(slidesRef, slides.length);

  return <>
    <Sidebar
      carouselAutoplay={carousel_autoplay}
      slides={slidesWithImages}
      setAttributes={setAttributes}
      currentSlide={currentSlide}
    />

      <SlidesContainer
        slides={slides}
        goToSlide={goToSlide}
        goToNextSlide={goToNextSlide}
        goToPrevSlide={goToPrevSlide}
        >
        { slidesWithImages?.map((slide, index) => {
          return <Slide
              key={index}
              index={index}
              ref={ element => slidesRef.current[index] = element }
              active={ currentSlide == index }
            >
              <EditableBackground
                image_url={slide.image_url}
                focalPoints={slide.focal_points}
                index={index}
                slides={slides}
                setAttributes={setAttributes}
              >
                <Caption
                  setAttributes={setAttributes}
                  slide={slide}
                  slides={slides}
                  index={index}
                  isSelected={isSelected}
                />
              </EditableBackground>
            </Slide>;
        })}

        <EditorControls
          slides={slides}
          addSlide={() => addSlide(slides, setAttributes)}
          removeSlide={() => removeSlide(slides, currentSlide, setAttributes, goToSlide)}
        />
      </SlidesContainer>
  </>;
}
