import { Covers } from './Covers';
import { useRef } from '@wordpress/element';
import { COVERS_LAYOUTS } from './CoversConstants';
import { useCovers } from './useCovers';
import { CoversCarouselControls } from './CoversCarouselControls';
import { CoversGridLoadMoreButton } from './CoversGridLoadMoreButton';

export const CoversFrontend = attributes => {
  const { initialRowsLimit, cover_type, title, description, covers, className, layout } = attributes;
  const coversContainerRef = useRef(null);

  const {
    showMoreCovers,
    row,
    amountOfCoversPerRow,
    slideCovers,
  } = useCovers(attributes, true, coversContainerRef);

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

  const showLoadMoreButton = !isCarouselLayout && !!initialRowsLimit && covers.length > (amountOfCoversPerRow * row);

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
        {showLoadMoreButton && <CoversGridLoadMoreButton showMoreCovers={showMoreCovers} />}
      </div>
    </section>
  );
}

