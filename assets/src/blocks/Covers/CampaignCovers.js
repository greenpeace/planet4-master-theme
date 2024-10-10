import {CoversImagePlaceholder} from './CoversImagePlaceholder';
import {IMAGE_SIZES} from './imageSizes';

const {__, sprintf} = wp.i18n;

export const CampaignCovers = ({
  covers,
  initialRowsLimit,
  row,
  inEditor = false,
  isCarouselLayout,
  amountOfCoversPerRow,
  isExample,
}) => (
  <div className="covers">
    {covers.map((cover, index) => {
      const {image, alt_text} = cover;
      const hideCover = !isCarouselLayout && !!initialRowsLimit && index >= row * amountOfCoversPerRow;

      if (hideCover) {
        return null;
      }

      const link = cover.link || cover.href;
      const srcset = cover.srcset || cover.src_set;
      const title = cover.title || cover.name;

      const campaignLink = inEditor ? null : link;

      return (
        <div key={link} className="campaign-card-column cover">
          <a
            href={campaignLink}
            data-ga-category="Campaign Covers"
            data-ga-action="Image"
            data-ga-label="n/a"
            aria-label={sprintf(__('Check our campaign about %s', 'planet4-blocks'), title)}
          >
            <div className="thumbnail-large">
              {image && image[0] && !isExample &&
                <img
                  loading="lazy"
                  sizes={IMAGE_SIZES.campaign}
                  srcSet={srcset}
                  src={image[0]}
                  alt={alt_text}
                  title={alt_text}
                />
              }
              {isExample && <CoversImagePlaceholder height={150} />}
              <span className="yellow-cta"><span aria-label="hashtag">#</span>{title}</span>
            </div>
          </a>
        </div>
      );
    })}
  </div>
);
