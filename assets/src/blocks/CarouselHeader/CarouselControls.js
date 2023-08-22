const {__} = wp.i18n;

export const CarouselControls = ({
  goToPrevSlide = () => {},
  goToNextSlide = () => {},
  goToSlide = () => {},
  currentSlide = null,
  slides = null,
}) => slides.length > 1 && (
  <>
    {/* Arrows */}
    <button className="carousel-control-prev" onClick={goToPrevSlide}>
      <span className="carousel-control-prev-icon" aria-hidden="true"><i></i></span>
      <span className="visually-hidden">{__('Previous', 'planet4-blocks')}</span>
    </button>
    <button className="carousel-control-next" onClick={goToNextSlide}>
      <span className="carousel-control-next-icon" aria-hidden="true"><i></i></span>
      <span className="visually-hidden">{__('Next', 'planet4-blocks')}</span>
    </button>
    {/* Indicators */}
    <div className="carousel-indicators-wrapper">
      <div className="container">
        <ol className="carousel-indicators">
          {
            slides.map((slide, index) =>
              <li
                onClick={() => goToSlide(index)}
                key={index}
                className={index === currentSlide ? 'active' : ''}
                role="presentation"
              ></li>
            )
          }
        </ol>
      </div>
    </div>
  </>
);
