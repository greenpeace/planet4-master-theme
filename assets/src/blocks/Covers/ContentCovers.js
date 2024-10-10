import {CoversImagePlaceholder} from './CoversImagePlaceholder';
import {IMAGE_SIZES} from './imageSizes';

const {__} = wp.i18n;

export const ContentCovers = ({
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
      const {
        link,
        srcset,
        alt_text,
        date_formatted,
      } = cover;

      const hideCover = !isCarouselLayout && !!initialRowsLimit && index >= row * amountOfCoversPerRow;

      if (hideCover) {
        return null;
      }

      const title = cover.title || cover.post_title;
      const image = cover.image || cover.thumbnail;
      const excerpt = cover.excerpt || cover.post_excerpt;

      const contentLink = inEditor ? null : link;

      return (
        <div key={link} className="post-column cover">
          <div className="content-covers-block-wrap clearfix">
            <div className="content-covers-block-info">
              <div className="content-covers-block-image" {...isExample && {style: {height: 120}}}>
                {image && !isExample &&
                  <a
                    href={contentLink}
                    data-ga-category="Content Covers"
                    data-ga-action="Image"
                    data-ga-label="n/a"
                    aria-label={__('Cover image, link to ', 'planet4-blocks') + title}
                  >
                    <img
                      loading="lazy"
                      src={image}
                      alt={alt_text}
                      title={alt_text}
                      srcSet={srcset}
                      sizes={IMAGE_SIZES.content}
                    />
                  </a>
                }
                {isExample && <CoversImagePlaceholder height="100%" />}
              </div>
              <div className="content-covers-block-information">
                {title &&
                  <h5>
                    <a
                      href={contentLink}
                      data-ga-category="Content Covers"
                      data-ga-action="Title"
                      data-ga-label="n/a"
                      dangerouslySetInnerHTML={{__html: title}}
                    />
                  </h5>
                }
                {date_formatted &&
                  <p className="publication-date">{date_formatted}</p>
                }
                {excerpt &&
                  <p className="post-excerpt" dangerouslySetInnerHTML={{__html: excerpt}} />
                }
              </div>
            </div>
          </div>
        </div>
      );
    })}
  </div>
);
