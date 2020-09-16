import { useHappypointImageData } from './useHappypointImageData';

export const HappypointFrontend = ({
  focus_image,
  opacity,
  mailing_list_iframe,
  iframe_url,
  id
}) => {
  const { imageData } = useHappypointImageData(id);

  const {
    background_src,
    background_srcset,
    background_alt,
    background_sizes,
    default_image,
    engaging_network_id
  } = imageData;

  let url = '';
  if (mailing_list_iframe || iframe_url) {
    url = iframe_url || engaging_network_id;
  }

  const imgProps = {
    src: background_src || default_image,
    style: {
      objectPosition: focus_image,
      opacity: opacity ? (opacity / 100) : 0.3,
    },
    alt: background_alt
  };

  if (background_src) {
    imgProps.srcSet = background_srcset || 'false';
    imgProps.sizes = background_sizes || 'false';
  }

  return (
    <section className="block block-footer block-wide happy-point-block-wrap">
      <picture>
        <img {...imgProps} loading="lazy" />
      </picture>
      <div className="container">
        <div className="row justify-content-md-center">
          {(mailing_list_iframe || url) &&
            <div className="col-md-10 happy-point" id="happy-point" data-src={url}>
              <iframe
                src={url}
                cellSpacing={0}
                allowtransparency="true"
                frameBorder={0}
                scrolling="no"
                width="100%"
              />
            </div>
          }
        </div>
      </div>
    </section>
  );
};
