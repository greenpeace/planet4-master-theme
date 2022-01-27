import { useHappypointImageData } from './useHappypointImageData';
import { HubspotEmbed } from './useHubspotEmbedCode';
import { useEffect } from '@wordpress/element';
import { useScript } from '../../components/useScript/useScript';

export const HappypointFrontend = ({
  focus_image,
  opacity,
  mailing_list_iframe,
  iframe_url,
  id,
  use_embed_code,
  embed_code,
  className,
}) => {
  const { imageData } = useHappypointImageData(id);

  const {
    background_src,
    background_srcset,
    background_alt,
    background_sizes,
    default_image,
    engaging_network_id,
    default_embed_code
  } = imageData;

  const imgProps = {
    src: background_src || default_image,
    style: {
      objectPosition: focus_image,
      opacity: opacity ? (opacity / 100) : 0.3,
    },
    alt: background_alt
  };

  if (background_src) {
    imgProps.srcSet = background_srcset || null;
    imgProps.sizes = background_sizes || null;
  }

  const url = iframe_url || engaging_network_id;

  return (
    <section className={`block block-footer block-wide happy-point-block-wrap ${className ?? ''}`}>
      <picture>
        <img {...imgProps} loading="lazy" />
      </picture>
      <div className="container">
        <div className="row justify-content-md-center">
          {use_embed_code
            ? <div className="col-md-10 mt-5" id="happy-point" />
            : <>
              {mailing_list_iframe && url && (
                <div className="col-md-10 happy-point" id="happy-point" data-src={url}>
                  <iframe
                    src={url}
                    cellSpacing={0}
                    allowtransparency="true"
                    frameBorder={0}
                    scrolling="no"
                    width="100%"
                    loading="lazy"
                  />
                </div>
              )}
            </>
          }
          <HubspotEmbed params={{use_embed_code: use_embed_code, embed_code: embed_code || default_embed_code, target: '#happy-point'}} />
        </div>
      </div>
    </section>
  );
};
