// Carousel Header
import {Slide} from './Slide';
import {Caption} from './Caption';
import {useSlides} from './useSlides';
import {CarouselControls} from './CarouselControls';

// Carousel Header Editor
import {Sidebar} from './Sidebar';
import {EditableBackground} from './EditableBackground';

const {useCallback, useMemo, useRef} = wp.element;

export const toSrcSet = sizes => {
  return sizes.map(size => `${size.url || size.source_url} ${size.width}w`).join();
};

export const CarouselHeaderEditor = ({setAttributes, attributes}) => {
  const {carousel_autoplay, slides, className} = attributes;
  const slidesRef = useRef([]);

  const {
    currentSlide,
    goToSlide,
    goToNextSlide,
    goToPrevSlide,
    autoplay,
    setAutoplay,
    handleAutoplay,
    indicatorsRef,
    handleUserInteraction,
  } = useSlides(slidesRef, slides.length);

  const changeSlideAttribute = useCallback((slideAttributeName, index) => value => {
    const newSlides = JSON.parse(JSON.stringify(slides));
    newSlides[index][slideAttributeName] = value;
    setAttributes({slides: newSlides});
  }, [setAttributes, slides]);

  const updateCurrentImageIndex = useCallback(index => {
    setAttributes({currentImageIndex: index});
  }, [setAttributes]);

  const changeSlideImage = useCallback((index, imageId, imageUrl, imageAlt, srcSet) => {
    const newSlides = [...slides];
    newSlides[index].image = imageId;
    newSlides[index].image_url = imageUrl;
    newSlides[index].image_alt = imageAlt;
    newSlides[index].image_srcset = srcSet;
    setAttributes({slides: newSlides});
  }, [setAttributes, slides]);

  const addSlide = useCallback(() => {
    const newSlides = slides.concat({
      image: null,
      focal_points: {},
      header: '',
      description: '',
      link_text: '',
      link_url: '',
      link_url_new_tab: false,
    });
    setAttributes({slides: newSlides});
    const lastSlide = newSlides.length - 1;
    // There is no callback to setAttributes so we use timeout instead
    setTimeout(() => goToSlide({newSlide: lastSlide}), 0);
  }, [slides, setAttributes, goToSlide]);

  const removeSlide = useCallback(() => {
    const newSlides = [
      ...slides.slice(0, currentSlide),
      ...slides.slice(currentSlide + 1),
    ];
    const lastSlide = newSlides.length - 1;
    setAttributes({slides: newSlides});
    goToSlide({newSlide: currentSlide > lastSlide ? 0 : currentSlide, forceCurrentSlide: true});
  }, [currentSlide, goToSlide, setAttributes, slides]);

  return useMemo(() => (
    <section className={`block block-header alignfull carousel-header ${className ?? ''}`}>
      <Sidebar
        carouselAutoplay={carousel_autoplay}
        slides={slides}
        setAttributes={setAttributes}
        currentSlide={currentSlide}
        changeSlideAttribute={changeSlideAttribute}
        goToSlide={goToSlide}
      />
      <div className="carousel-wrapper-header">
        <ul className="carousel-inner" role="listbox">
          {slides?.map((slide, index) => {
            const isActive = currentSlide === index;

            return (
              <Slide
                key={index}
                ref={element => slidesRef ? slidesRef.current[index] = element : null}
                active={isActive}
                focusable={isActive}
                handleUserInteraction={handleUserInteraction}
              >
                <EditableBackground
                  image_url={slide.image_url}
                  image_srcset={slide.image_srcset}
                  focalPoints={slide.focal_points}
                  image_id={slide.image}
                  index={index}
                  changeSlideImage={changeSlideImage}
                  updateCurrentImageIndex={updateCurrentImageIndex}
                  addSlide={addSlide}
                  removeSlide={removeSlide}
                  slides={slides}
                />
                <Caption
                  slide={slide}
                  index={index}
                  changeSlideAttribute={changeSlideAttribute}
                />
              </Slide>);
          })}
          {(slides.length > 1) && (
            <CarouselControls
              goToPrevSlide={goToPrevSlide}
              goToNextSlide={goToNextSlide}
              setAutoplay={setAutoplay}
              goToSlide={goToSlide}
              handleAutoplay={handleAutoplay}
              slides={slides}
              currentSlide={currentSlide}
              autoplay={autoplay}
              autoplayToggle={carousel_autoplay}
              ref={indicatorsRef}
            />
          )}
        </ul>
      </div>
    </section>
  ), [
    autoplay,
    carousel_autoplay,
    className,
    currentSlide,
    slides,
    goToSlide,
    goToPrevSlide,
    goToNextSlide,
    setAttributes,
    addSlide,
    changeSlideAttribute,
    changeSlideImage,
    removeSlide,
    updateCurrentImageIndex,
    setAutoplay,
    indicatorsRef,
    handleAutoplay,
    handleUserInteraction,
  ]);
};
