import {Covers} from './Covers';
import {COVERS_LAYOUTS} from './CoversConstants';
import {useCovers} from './useCovers';
import {CoversGridLoadMoreButton} from './CoversGridLoadMoreButton';
import {CoversCarouselLayout} from './CoversCarouselLayout';

export const CoversFrontend = ({attributes}) => {
  const {
    initialRowsLimit,
    cover_type,
    title,
    description,
    covers = [],
    className,
    layout,
    readMoreText,
  } = attributes;

  const {
    showMoreCovers,
    row,
    amountOfCoversPerRow,
    isSmallWindow,
  } = useCovers(attributes, true);

  const isCarouselLayout = layout === COVERS_LAYOUTS.carousel;

  const coversProps = {
    covers,
    initialRowsLimit,
    row,
    showMoreCovers,
    cover_type,
    isCarouselLayout,
    amountOfCoversPerRow,
  };

  if (!covers.length) {
    return null;
  }

  const showLoadMoreButton = !isCarouselLayout && !!initialRowsLimit && covers.length > (amountOfCoversPerRow * row);

  return (
    <section className={`block covers-block ${cover_type}-covers-block ${className ?? ''} ${layout}-layout`}>
      {title &&
        <h2 className="page-section-header" dangerouslySetInnerHTML={{__html: title}} />
      }
      {description &&
        <p className="page-section-description" dangerouslySetInnerHTML={{__html: description}} />
      }
      {isCarouselLayout && !isSmallWindow ? <CoversCarouselLayout {...coversProps} /> : <Covers {...coversProps} />}
      {showLoadMoreButton && <CoversGridLoadMoreButton showMoreCovers={showMoreCovers} readMoreText={readMoreText} />}
    </section>
  );
};

