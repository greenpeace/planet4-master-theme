const {useMemo} = wp.element;
const {__, sprintf} = wp.i18n;

export const CarouselControls = ({
  goToPrevSlide = () => {},
  goToNextSlide = () => {},
  goToSlide = () => {},
  handleAutoplay = () => {},
  currentSlide = null,
  slides = null,
  autoplay,
  disableControls,
}) => useMemo(() => (
  <>
    {/* Arrows */}
    <button className="carousel-control-prev" onClick={goToPrevSlide} aria-label={__('Go to previous slide', 'planet4-master-theme')}>
      <span className="carousel-control-prev-icon" aria-hidden="true"><i></i></span>
      <span className="visually-hidden">{__('Previous', 'planet4-master-theme')}</span>
    </button>
    <button className="carousel-control-next" onClick={goToNextSlide} aria-label={__('Go to next slide', 'planet4-master-theme')}>
      <span className="carousel-control-next-icon" aria-hidden="true"><i></i></span>
      <span className="visually-hidden">{__('Next', 'planet4-master-theme')}</span>
    </button>
    {/* Indicators */}
    <div className="carousel-indicators-wrapper">
      <div className="container">
        <ol className="carousel-indicators" tabIndex={-1}>
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
                      goToSlide(index);
                    }
                  }}
                  tabIndex={0}
                  onKeyDown={e => {
                    if ((e.key === 'Enter' || e.key === ' ') && index !== currentSlide) {
                      e.preventDefault();
                      goToSlide(index);
                    }
                  }}
                  // translators: %s: slide index
                  aria-label={sprintf(__('Go to slide %s', 'planet4-master-theme'), index + 1)}
                  aria-current={index === currentSlide ? 'true' : undefined}
                />
              </li>
            )
          }
        </ol>
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
      </div>
    </div>
  </>
), [currentSlide, disableControls, autoplay, slides, goToPrevSlide, goToNextSlide, goToSlide, handleAutoplay]);
