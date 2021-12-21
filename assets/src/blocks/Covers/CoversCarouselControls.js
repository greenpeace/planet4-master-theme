const { __ } = wp.i18n;

const CoversCarouselArrow = ({ handler, direction, disabled = false }) => (
  <button className={`carousel-control-${direction}`} disabled={disabled} onClick={handler}>
    <span className={`carousel-control-${direction}-icon`} aria-hidden="true"><i></i></span>
    <span className="visually-hidden">{__(direction === 'next' ? 'Next' : 'Prev', 'planet4-blocks')}</span>
  </button>
);

export const CoversCarouselControls = ({
  totalAmountOfCovers,
  amountOfCoversPerRow,
  slideCovers,
  currentRow,
}) => {
  if (!amountOfCoversPerRow || totalAmountOfCovers <= amountOfCoversPerRow) {
    return null;
  }

  const amountOfSlides = Math.ceil(totalAmountOfCovers / amountOfCoversPerRow);
  const indicators = Array.from(Array(amountOfSlides).keys());

  return (
    <>
      <CoversCarouselArrow
        direction='prev'
        disabled={currentRow === 1 || !slideCovers}
        {...slideCovers && {
          handler: () => slideCovers('prev')
        }}
      />
      <CoversCarouselArrow
        direction='next'
        disabled={currentRow === amountOfSlides || !slideCovers}
        {...slideCovers && {
          handler: () => slideCovers('next')
        }}
      />
      <ol className="carousel-indicators">
        {indicators.map(index =>
          <li
            key={`indicator-${index}`}
            className={`${index === currentRow - 1 ? 'active' : ''} ${!slideCovers ? 'disabled' : ''}`}
            {...slideCovers && {
              onClick: () => slideCovers('', index + 1)
            }}
          />
        )}
      </ol>
    </>
  );
}
