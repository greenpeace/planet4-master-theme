const {useMemo, forwardRef, useRef} = wp.element;
const {__, sprintf} = wp.i18n;

export const CarouselControls = forwardRef(({
  goToPrevSlide = () => {},
  goToNextSlide = () => {},
  goToSlide = () => {},
  handleAutoplay = () => {},
  setAutoplay = () => {},
  currentSlide = null,
  slides = null,
  autoplay,
  autoplayToggle,
}, ref) => {
  const controlsRef = useRef();

  return useMemo(() => (
    <>
      {/* Indicators */}
      <div className="carousel-indicators-wrapper">
        <div className="container">
          {autoplayToggle && (
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
                    onClick={e => {
                      setAutoplay(false);
                      // eslint-disable-next-line @wordpress/no-global-active-element
                      if (e.target === document.activeElement) {
                        e.preventDefault();
                      }
                      if (index !== currentSlide) {
                        // eslint-disable-next-line @wordpress/no-global-active-element
                        goToSlide({newSlide: index, fromTab: e.target === document.activeElement});
                      }
                    }}
                    tabIndex={0}
                    aria-label={sprintf(
                      // translators: %s: slide header
                      __('Go to %s', 'planet4-master-theme'), slides[index].header ||
                      sprintf(
                        // translators: %d: slide number
                        __('slide %d', 'planet4-master-theme'), index + 1)
                    )}
                  />
                </li>
              )
            }
          </ol>
        </div>
      </div>
      {/* Arrows */}
      <nav aria-label={__('Greenpeace highlights carousel controls', 'planet4-master-theme')} ref={controlsRef}>
        <button
          className="carousel-control-prev"
          onClick={e => {
            // eslint-disable-next-line @wordpress/no-global-active-element
            goToPrevSlide(e.target === document.activeElement);
            setAutoplay(false);
          }}
          // translators: %s: slide header
          aria-label={sprintf(__('Go to previous slide %s', 'planet4-master-theme'), slides[currentSlide - 1] && slides[currentSlide - 1].header ? slides[currentSlide - 1].header : slides[slides.length - 1].header)}
        >
          <span className="carousel-control-prev-icon" aria-hidden="true"><i></i></span>
          <span className="visually-hidden">{__('Previous', 'planet4-master-theme')}</span>
        </button>
        <button
          className="carousel-control-next"
          onClick={e => {
            // eslint-disable-next-line @wordpress/no-global-active-element
            goToNextSlide(e.target === document.activeElement);
            setAutoplay(false);
          }}
          // translators: %s: slide header
          aria-label={sprintf(__('Go to next slide %s', 'planet4-master-theme'), slides[currentSlide + 1] && slides[currentSlide + 1].header ? slides[currentSlide + 1].header : slides[0].header)}
        >
          <span className="carousel-control-next-icon" aria-hidden="true"><i></i></span>
          <span className="visually-hidden">{__('Next', 'planet4-master-theme')}</span>
        </button>
      </nav>

      {/* This will help screen readers to announce the current slide */}
      {!autoplay && (
        <div aria-live="polite" aria-atomic="true" className="visually-hidden">
          {
            // translators: %d: slide number
            sprintf(__('Slide %d', 'planet4-master-theme'), currentSlide + 1)
          }
        </div>
      )}
    </>
  ), [
    currentSlide,
    setAutoplay,
    autoplayToggle,
    autoplay,
    slides,
    ref,
    controlsRef,
    goToPrevSlide,
    goToNextSlide,
    goToSlide,
    handleAutoplay,
  ]);
});
