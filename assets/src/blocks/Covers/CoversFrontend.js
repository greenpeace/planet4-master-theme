import { Covers } from './Covers';
import { useRef, useEffect } from '@wordpress/element';
import { COVERS_LAYOUTS, COVERS_TYPES } from './CoversConstants';
import { useCovers } from './useCovers';
import { CoversCarouselControls } from './CoversCarouselControls';
import { CoversGridLoadMoreButton } from './CoversGridLoadMoreButton';

export const CoversFrontend = attributes => {
  const { initialRowsLimit, cover_type, title, description, covers, className, layout, readMoreText } = attributes;
  const coversContainerRef = useRef(null);

  const {
    showMoreCovers,
    row,
    amountOfCoversPerRow,
    setRow,
  } = useCovers(attributes, true);

  const isCarouselLayout = layout === COVERS_LAYOUTS.carousel;

  const coversProps = {
    covers,
    initialRowsLimit,
    row,
    showMoreCovers,
    cover_type,
    isCarouselLayout,
    amountOfCoversPerRow
  };

  if (!covers.length) {
    return null;
  }

  const slideCovers = (direction, rowNumber) => {
    if (!coversContainerRef.current) {
      return;
    }

    const covers = coversContainerRef.current.querySelector('.covers');

    if (!covers) {
      return;
    }

    const isRTL = document.querySelector('html').dir === 'rtl';
    // We need to account for the spacing between covers,
    // in all styles that spacing is 24px for screens >= 992px.
    let scrollOffset = (isRTL ? -1 : 1) * (covers.offsetWidth + 24);

    // For the Take Action covers because of the box-shadow we had to add
    // extra padding to the sides (5px each) so we need to account for them.
    if (cover_type === COVERS_TYPES.takeAction) {
      scrollOffset += (isRTL ? 1 : -1) * 10;
    }

    if (direction) {
      const initialScrollPosition = covers.scrollLeft;
      covers.scrollLeft = initialScrollPosition + (direction === 'next' ? scrollOffset : -scrollOffset);
      setRow(direction === 'next' ? row + 1 : row - 1);
    } else if (rowNumber) {
      covers.scrollLeft = (rowNumber - 1) * scrollOffset;
      setRow(rowNumber);
    }
  };

  const showLoadMoreButton = !isCarouselLayout && !!initialRowsLimit && covers.length > (amountOfCoversPerRow * row);

  useEffect(() => {
    if (!isCarouselLayout) {
      return;
    }

    const onResizeHandler = () => {
      slideCovers('', 1);
    }

    window.addEventListener('resize', onResizeHandler);
    return () => window.removeEventListener('resize', onResizeHandler);
  }, []);

  return (
    <section
      className={`block covers-block ${cover_type}-covers-block ${className ?? ''} ${layout}-layout`}
      ref={coversContainerRef}
    >
      {title &&
        <h2 className='page-section-header' dangerouslySetInnerHTML={{ __html: title }} />
      }
      {description &&
        <div className='page-section-description' dangerouslySetInnerHTML={{ __html: description }} />
      }
      <div className='covers-container'>
        <Covers {...coversProps} />
        {isCarouselLayout &&
          <CoversCarouselControls
            currentRow={row}
            slideCovers={slideCovers}
            amountOfCoversPerRow={amountOfCoversPerRow}
            totalAmountOfCovers={covers.length}
          />
        }
        {showLoadMoreButton && <CoversGridLoadMoreButton showMoreCovers={showMoreCovers} readMoreText={readMoreText} />}
      </div>
    </section>
  );
}

