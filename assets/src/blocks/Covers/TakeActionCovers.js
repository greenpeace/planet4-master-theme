import { IMAGE_SIZES } from './imageSizes';

const { __ } = wp.i18n;

export const TakeActionCovers = ({
  initialRowsLimit,
  covers,
  row,
  inEditor = false,
  isCarouselLayout,
  amountOfCoversPerRow,
}) => (
  <div className='covers'>
    {covers.map((cover, index) => {
      const {
        button_link,
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

      const buttonLink = inEditor ? null : button_link;

      return (
        <div key={title} className='cover-card cover'>
          <a
            className='cover-card-overlay'
            data-ga-category='Take Action Covers'
            data-ga-action='Card'
            data-ga-label='n/a'
            href={buttonLink}
            aria-label={__('Take action cover, link to ' + title, 'planet4-blocks')}
          />
          <a
            data-ga-category='Take Action Covers'
            data-ga-action='Image'
            data-ga-label='n/a'
            href={buttonLink}
            aria-label={__('Take action cover, link to ' + title, 'planet4-blocks')}
          >
            <img
              alt={alt_text}
              src={image}
              srcSet={srcset}
              sizes={IMAGE_SIZES.takeAction}
            />
          </a>
          <div className='cover-card-content'>
            {/* Regardless of how many tags there are, we only show the first one */}
            {tags && tags.length > 0 && <span className='cover-card-tag'>{tags[0].name}</span>}
            <a
              className='cover-card-heading'
              data-ga-category='Take Action Covers'
              data-ga-action='Title'
              data-ga-label='n/a'
              href={buttonLink}
            >
              {title}
            </a>
            <p className="cover-card-excerpt">{excerpt}</p>
          </div>
          <a
            className='btn cover-card-btn btn-primary'
            data-ga-category='Take Action Covers'
            data-ga-action='Call to Action'
            data-ga-label='n/a'
            href={buttonLink}
          >
            {button_text}
          </a>
        </div>
      );
    })}
  </div>
);
