import { Component } from '@wordpress/element';
import _uniqueId from 'lodash/uniqueId';

const { __ } = wp.i18n;

export class GalleryCarousel extends Component {
  constructor(props) {
    super(props);
    this.id = `gallery_${_uniqueId()}`;
  }

  render() {
    const { images } = this.props;
    return (
      <div id={this.id} className="carousel slide" data-ride="carousel">
        {images.length > 1 &&
          <ol className="carousel-indicators">
            {images.map((image, index) =>
              <li key={`#${this.id}-${index}`} data-target={`#${this.id}`} data-slide-to={index} className={index === 0 ? 'active' : ''} />
            )}
          </ol>
        }
        <div className="carousel-inner" role="listbox">
          {images.length > 1 &&
            <a className="carousel-control-prev" href={`#${this.id}`} role="button" data-slide="prev">
              <span className="carousel-control-prev-icon" aria-hidden="true"><i></i></span>
              <span className="sr-only">{__('Previous', 'planet4-blocks')}</span>
            </a>
          }
          {images.map((image, index) => (
            <div key={image.image_src} className={`carousel-item ${index === 0 ? 'active' : ''}`}>
              <img
                src={image.image_src}
                srcSet={image.image_srcset}
                sizes={image.image_sizes || 'false'}
                style={{ objectPosition: image.focus_image }}
                alt={image.alt_text}
              />

              {(image.caption || image.credits) && (
                <div className="carousel-caption">
                  <p>
                    {image.caption || image.credits}
                  </p>
                </div>
              )}
            </div>
          ))}
          {images.length > 1 && (
            <a className="carousel-control-next" href={`#${this.id}`} role="button" data-slide="next">
              <span className="carousel-control-next-icon" aria-hidden="true"><i></i></span>
              <span className="sr-only">{__('Next', 'planet4-blocks')}</span>
            </a>
          )}
        </div>
      </div>
    );
  }
}
