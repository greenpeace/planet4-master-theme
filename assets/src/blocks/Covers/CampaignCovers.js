import { IMAGE_SIZES } from './imageSizes';

const { __ } = wp.i18n;

export const CampaignCovers = ({
  covers,
  initialRowsLimit,
  row,
  inEditor = false,
  isCarouselLayout,
  amountOfCoversPerRow,
}) => (
  <div className='covers'>
    {covers.map((cover, index) => {
      const { href, image, alt_text, name, src_set } = cover;
      const hideCover = !isCarouselLayout && !!initialRowsLimit && index >= row * amountOfCoversPerRow;

      if (hideCover) {
        return null;
      }

      const campaignLink = inEditor ? null : href;

      return (
        <div key={name} className='cover'>
          <a
            href={campaignLink}
            data-ga-category='Campaign Covers'
            data-ga-action='Image'
            data-ga-label='n/a'
            aria-label={__('Check our campaign about ' + name, 'planet4-blocks')}
          >
            <div className='thumbnail-large'>
              {image && image[0] &&
                <img
                  loading='lazy'
                  sizes={IMAGE_SIZES.campaign}
                  srcSet={src_set}
                  src={image[0]}
                  alt={alt_text}
                  title={alt_text}
                />
              }
              <span className='yellow-cta'><span aria-label='hashtag'>#</span>{name}</span>
            </div>
          </a>
        </div>
      );
    })}
  </div>
);
