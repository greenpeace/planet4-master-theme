const {useMemo, forwardRef, useRef} = wp.element;
const {__, sprintf} = wp.i18n;

export const CarouselControls = forwardRef(({
  goToPrevSlide = () => {},
  goToNextSlide = () => {},
  goToSlide = () => {},
  handleAutoplay = () => {},
  currentSlide = null,
  slides = null,
  autoplay,
  disableControls,
}, ref) => {
  const controlsRef = useRef();

  return useMemo(() => (
    <>
      {/* Indicators */}
      <div className="carousel-indicators-wrapper">
        <div className="container">
          {disableControls && (
            <>
              <button
                aria-label={__('Autoplay', 'planet4-master-theme')}
                className={`carousel-autoplay-control ${autoplay ? 'stop' : 'play'}`}
                onClick={handleAutoplay}
                aria-pressed={autoplay ? 'true' : 'false'}
              />
              {/* This is for screen readers to announce the state of the autoplay */}
              <div className="visually-hidden" aria-live="polite">
                {autoplay ? __('Slide resumed', 'planet4-master-theme') : __('Slide paused', 'planet4-master-theme')}
              </div>
            </>
          )}
          <ol className="carousel-indicators" tabIndex={-1} ref={ref}>
            {
              slides.map((_, index) =>
                <li
                  key={index}
                  {...(index === currentSlide) ? {
                    className: 'active',
                  } : {}}
                >
                  <button
                    onClick={() => {
                      if (index !== currentSlide) {
                        goToSlide({newSlide: index, fromClick: true});
                      }
                    }}
                    tabIndex={0}
                    onKeyDown={e => {
                      if ((e.key === 'Enter' || e.key === ' ') && index !== currentSlide) {
                        e.preventDefault();
                        goToSlide({newSlide: index, fromClick: true});
                      }
                    }}
                    // translators: %s: slide header
                    aria-label={sprintf(__('Go to %s slide', 'planet4-master-theme'), slides[index].header)}
                    aria-current={index === currentSlide ? 'true' : undefined}
                  />
                </li>
              )
            }
          </ol>
        </div>
      </div>
      {/* Arrows */}
      <nav aria-label={__('Greenpeace highlights carousel controls', 'planet4-master-theme')} ref={controlsRef}>

        <button className="carousel-control-prev" onClick={goToPrevSlide}
          // translators: %s: slide header
          aria-label={sprintf(__('Go to previous slide %s', 'planet4-master-theme'), slides[currentSlide - 1] && slides[currentSlide - 1].header ? slides[currentSlide - 1].header : slides[slides.length - 1].header)}
        >
          <span className="carousel-control-prev-icon" aria-hidden="true"><i></i></span>
          <span className="visually-hidden">{__('Previous', 'planet4-master-theme')}</span>
        </button>
        <button className="carousel-control-next" onClick={goToNextSlide}
          // translators: %s: slide header
          aria-label={sprintf(__('Go to next slide %s', 'planet4-master-theme'), slides[currentSlide + 1] && slides[currentSlide + 1].header ? slides[currentSlide + 1].header : slides[0].header)}
        >
          <span className="carousel-control-next-icon" aria-hidden="true"><i></i></span>
          <span className="visually-hidden">{__('Next', 'planet4-master-theme')}</span>
        </button>
      </nav>

    </>
  ), [currentSlide, disableControls, autoplay, slides, ref, controlsRef, goToPrevSlide, goToNextSlide, goToSlide, handleAutoplay]);
});
