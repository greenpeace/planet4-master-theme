import { useState, useEffect, useRef } from '@wordpress/element';

const { __ } = wp.i18n;

// This will trigger the browser to synchronously calculate the style and layout
// You can find a list of examples here: https://gist.github.com/paulirish/5d52fb081b3570c81e3a
const reflow = element => element.offsetHeight;

export const GalleryCarousel = ({ images }) => {
  const [currentSlide, setCurrentSlide] = useState(0);
  const [sliding, setSliding] = useState(false);

  const lastSlide = images.length - 1;

  const getOrder = newSlide => {
    let order = newSlide < currentSlide ? 'prev' : 'next';
    if (newSlide === lastSlide && currentSlide === 0 && order !== 'prev') {
      order = 'prev';
    } else if (newSlide === 0 && currentSlide === lastSlide && order !== 'next') {
      order = 'next';
    }
    return order;
  }

  const goToSlide = newSlide => {
    const nextElement = slidesRef.current[newSlide];
    const activeElement = slidesRef.current[currentSlide];
    if (newSlide !== currentSlide && nextElement && activeElement && !sliding) {
      setSliding(true);
      const order = getOrder(newSlide);
      const direction = order === 'next' ? 'left' : 'right';
      const orderClassname = `carousel-item-${order}`;
      const directionClassname = `carousel-item-${direction}`;

      nextElement.classList.add(orderClassname);

      reflow(nextElement);

      activeElement.classList.add(directionClassname);
      nextElement.classList.add(directionClassname);

      setTimeout(() => {
        nextElement.classList.remove(directionClassname, orderClassname);
        activeElement.classList.remove(orderClassname, directionClassname);
        setSliding(false);
        setCurrentSlide(newSlide);
      }, 600);
    }
  }

  const goToNextSlide = () => goToSlide(currentSlide === lastSlide ? 0 : currentSlide + 1);
  const goToPrevSlide = () => goToSlide(currentSlide === 0 ? lastSlide : currentSlide - 1);

  const timerRef = useRef(null);
  const slidesRef = useRef([]);

  // Set up the autoplay for the slides
  useEffect(() => {
    if (images.length > 1) {
      if (timerRef.current) {
        clearTimeout(timerRef.current);
      }
      timerRef.current = setTimeout(goToNextSlide, 10000);
      return () => clearTimeout(timerRef.current);
    }
  }, [currentSlide, images]);

  return (
    <div className="carousel slide">
      {images.length > 1 &&
        <ol className="carousel-indicators">
          {images.map((image, index) =>
            <li
              key={`indicator-${index}`}
              onClick={() => goToSlide(index)}
              className={index === currentSlide ? 'active' : ''}
            />
          )}
        </ol>
      }
      <div className="carousel-inner" role="listbox">
        {images.length > 1 &&
          <a className="carousel-control-prev" role="button" onClick={goToPrevSlide}>
            <span className="carousel-control-prev-icon" aria-hidden="true"><i></i></span>
            <span className="sr-only">{__('Previous', 'planet4-blocks')}</span>
          </a>
        }
        {images.map((image, index) => (
          <div
            key={image.image_src}
            className={`carousel-item ${index === currentSlide ? 'active' : ''}`}
            ref={element => slidesRef.current[index] = element}
          >
            <img
              loading='lazy'
              src={image.image_src}
              srcSet={image.image_srcset}
              sizes={image.image_sizes || 'false'}
              style={{ objectPosition: image.focus_image }}
              alt={image.alt_text}
            />

            {(image.caption || image.credits) && (
              <div className="carousel-caption">
                <p>
                  {image.caption || image.credits}
                </p>
              </div>
            )}
          </div>
        ))}
        {images.length > 1 && (
          <a className="carousel-control-next" role="button" onClick={goToNextSlide}>
            <span className="carousel-control-next-icon" aria-hidden="true"><i></i></span>
            <span className="sr-only">{__('Next', 'planet4-blocks')}</span>
          </a>
        )}
      </div>
    </div>
  );
}
