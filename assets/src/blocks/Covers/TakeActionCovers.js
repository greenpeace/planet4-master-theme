import {CoversImagePlaceholder} from './CoversImagePlaceholder';
import {IMAGE_SIZES} from './imageSizes';

const {__, sprintf} = wp.i18n;

export const TakeActionCovers = ({
  initialRowsLimit,
  covers,
  row,
  inEditor = false,
  isCarouselLayout,
  amountOfCoversPerRow,
  isExample,
}) => (
  <div className="covers">
    {covers.map((cover, index) => {
      const {
        title,
        tags,
        image,
        srcset,
        alt_text,
        excerpt,
        button_text,
      } = cover;
      const hideCover = !isCarouselLayout && !!initialRowsLimit && index >= row * amountOfCoversPerRow;

      if (hideCover) {
        return null;
      }

      const link = cover.link || cover.button_link;

      const buttonLink = inEditor ? null : link;

      return (
        <div key={link} className="cover-card cover">
          {/* eslint-disable-next-line jsx-a11y/anchor-has-content */}
          <a
            className="cover-card-overlay"
            data-ga-category="Take Action Covers"
            data-ga-action="Card"
            data-ga-label="n/a"
            href={buttonLink}
            // translators: cover title
            aria-label={sprintf(__('Take action cover, link to %s', 'planet4-blocks'), title)}
          />
          <a
            data-ga-category="Take Action Covers"
            data-ga-action="Image"
            data-ga-label="n/a"
            href={buttonLink}
            // translators: cover title
            aria-label={sprintf(__('Take action cover, link to %s', 'planet4-blocks'), title)}
          >
            {isExample ?
              <CoversImagePlaceholder height={220} /> :
              <img
                loading="lazy"
                alt={alt_text || undefined}
                src={image || undefined}
                srcSet={srcset || undefined}
                sizes={IMAGE_SIZES.takeAction}
              />
            }
          </a>
          <div className="cover-card-content">
            {/* Regardless of how many tags there are, we only show the first one */}
            {tags && tags.length > 0 && <span className="cover-card-tag">{tags[0].name}</span>}
            <a
              className="cover-card-heading"
              data-ga-category="Take Action Covers"
              data-ga-action="Title"
              data-ga-label="n/a"
              href={buttonLink}
              dangerouslySetInnerHTML={{__html: title}}
            />
            <p className="cover-card-excerpt" dangerouslySetInnerHTML={{__html: excerpt}} />
          </div>
          <a
            className="btn cover-card-btn btn-primary"
            data-ga-category="Take Action Covers"
            data-ga-action="Call to Action"
            data-ga-label="n/a"
            href={buttonLink}
          >
            {button_text}
          </a>
        </div>
      );
    })}
  </div>
);
