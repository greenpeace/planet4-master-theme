const {useState, useEffect, useMemo} = wp.element;
const {__} = wp.i18n;

export const CarouselControls = ({
  goToPrevSlide = () => {},
  goToNextSlide = () => {},
  goToSlide = () => {},
  handleAutoplay = () => {},
  currentSlide = null,
  slides = [],
  autoplay,
}) => {
  const [isPaused, setIsPaused] = useState(false);
  const [hasAutoplay, setHasAutoplay] = useState(false);

  useEffect(() => {
    if (autoplay) {
      setHasAutoplay(true);
    }
  }, [autoplay]);

  const handlePausePlayButton = () => {
    setIsPaused(prev => !prev);
    handleAutoplay();
  };

  return useMemo(
    () => (
      <>
        {/* Arrows */}
        <button
          className="carousel-control-prev"
          onClick={goToPrevSlide}
          aria-label="Go to previous slide"
        >
          <span className="carousel-control-prev-icon" aria-hidden="true">
            <i></i>
          </span>
          <span className="visually-hidden">
            {__('Previous', 'planet4-blocks')}
          </span>
        </button>
        <button
          className="carousel-control-next"
          onClick={goToNextSlide}
          aria-label="Go to next slide"
        >
          <span className="carousel-control-next-icon" aria-hidden="true">
            <i></i>
          </span>
          <span className="visually-hidden">
            {__('Next', 'planet4-blocks')}
          </span>
        </button>

        {/* Indicators */}
        <div className="carousel-indicators-wrapper">
          <div className="container">
            <ol className="carousel-indicators" tabIndex={-1}>
              {slides.map((_, index) => (
                <li
                  key={index}
                  {...(index === currentSlide ? {className: 'active'} : {})}
                >
                  <button
                    onClick={() => {
                      if (index !== currentSlide) {
                        goToSlide(index);
                      }
                    }}
                    tabIndex={0}
                    onKeyDown={e => {
                      if (
                        (e.key === 'Enter' || e.key === ' ') &&
                        index !== currentSlide
                      ) {
                        e.preventDefault();
                        goToSlide(index);
                      }
                    }}
                    aria-label={__('Go to slide', 'planet4-blocks') + ` ${index + 1}`}
                    aria-current={index === currentSlide ? 'true' : undefined}
                  />
                </li>
              ))}
            </ol>
            {hasAutoplay && (
              <button
                aria-label={isPaused ?
                  __('Play Slider', 'planet4-blocks') :
                  __('Stop Slider', 'planet4-blocks')}
                className={`carousel-autoplay-control ${
                  isPaused ? 'play' : 'stop'
                }`}
                onClick={handlePausePlayButton}
              />
            )}
          </div>
        </div>
      </>
    ),
    [
      currentSlide,
      slides,
      goToPrevSlide,
      goToNextSlide,
      goToSlide,
      isPaused,
      hasAutoplay,
    ]
  );
};
