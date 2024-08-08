// Carousel Header
import {Slide} from './Slide';
import {Caption} from './Caption';
import {useSlides} from './useSlides';
import {CarouselControls} from './CarouselControls';

// Carousel Header Editor
import {Sidebar} from './Sidebar';
import {EditableBackground} from './EditableBackground';

const {useSelect} = wp.data;
const {useRef} = wp.element;

export const toSrcSet = sizes => {
  return sizes.map(size => `${size.url || size.source_url} ${size.width}w`).join();
};

export const CarouselHeaderEditor = ({setAttributes, attributes}) => {
  const {carousel_autoplay, slides, className} = attributes;
  const slidesRef = useRef([]);

  const {currentSlide, goToSlide, goToNextSlide, goToPrevSlide} = useSlides(slidesRef, slides.length - 1);

  const changeSlideAttribute = (slideAttributeName, index) => value => {
    const newSlides = JSON.parse(JSON.stringify(slides));
    newSlides[index][slideAttributeName] = value;
    setAttributes({slides: newSlides});
  };

  const updateCurrentImageIndex = index => {
    setAttributes({currentImageIndex: index});
  };

  const changeSlideImage = (index, imageId, imageUrl, imageAlt, srcSet) => {
    const newSlides = [...slides];
    newSlides[index].image = imageId;
    newSlides[index].image_url = imageUrl;
    newSlides[index].image_alt = imageAlt;
    newSlides[index].image_srcset = srcSet;
    setAttributes({slides: newSlides});
  };

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
    setAttributes({slides: newSlides});
    const lastSlide = newSlides.length - 1;
    // There is no callback to setAttributes so we use timeout instead
    setTimeout(() => goToSlide(lastSlide), 0);
  };

  const removeSlide = () => {
    const newSlides = [
      ...slides.slice(0, currentSlide),
      ...slides.slice(currentSlide + 1),
    ];
    const lastSlide = newSlides.length - 1;
    setAttributes({slides: newSlides});
    goToSlide(currentSlide > lastSlide ? 0 : currentSlide, true);
  };

  const needsMigration = slides.some(slide => !!slide.image && !slide.image_srcset);
  const migratedSlides = useSelect(select => slides && slides.map(slide => {
    if (!needsMigration) {
      return null;
    }
    let attempt = 0;
    let image;
    // Run a loop as this could return undefined the first time.
    while (!image && attempt++ < 100) {
      image = select('core').getMedia(slide.image);
    }
    // Didn't see this case occur but catch it anyway.
    if (!image) {
      return slide;
    }
    const image_srcset = toSrcSet(Object.values(image.media_details.sizes));
    return ({...slide, image_url: image.source_url, image_srcset, image_alt: image.alt_text});
  }), [needsMigration]);

  return (
    <section className={`block block-header alignfull carousel-header ${className ?? ''}`}>
      <Sidebar
        carouselAutoplay={carousel_autoplay}
        slides={slides}
        setAttributes={setAttributes}
        currentSlide={currentSlide}
        changeSlideAttribute={changeSlideAttribute}
        goToSlide={goToSlide}
      />
      { needsMigration && <button
        title={'This block was created before WYSIWYG, press this to fetch data the new version uses.'}
        onClick={() => {
          setAttributes({slides: migratedSlides});
        }}
      >Migrate image data</button>}
      <div className="carousel-wrapper-header">
        <div className="carousel-inner" role="listbox">
          {slides?.map((slide, index) => (
            <Slide
              key={index}
              ref={element => slidesRef ? slidesRef.current[index] = element : null}
              active={currentSlide === index}
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
            </Slide>
          ))}
          <CarouselControls
            goToPrevSlide={() => goToPrevSlide(carousel_autoplay)}
            goToNextSlide={() => goToNextSlide(carousel_autoplay)}
            goToSlide={goToSlide}
            slides={slides}
            currentSlide={currentSlide}
          />
        </div>
      </div>
    </section>
  );
};
