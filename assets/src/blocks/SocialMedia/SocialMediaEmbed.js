import {OEMBED_EMBED_TYPE, FACEBOOK_EMBED_TYPE} from './SocialMediaConstants.js';

export const SocialMediaEmbed = ({
  alignmentClass,
  embedCode,
  facebookPageTab,
  facebookPageUrl,
  embedType,
}) => {
  if (
    (embedType === OEMBED_EMBED_TYPE && !embedCode) ||
    (embedType === FACEBOOK_EMBED_TYPE && !facebookPageUrl)
  ) {
    return null;
  }

  return (
    <div className={`social-media-embed ${alignmentClass ?? ''}`}>
      {(embedType === OEMBED_EMBED_TYPE && embedCode) ?
        <div dangerouslySetInnerHTML={{__html: embedCode}} /> :
        <>
          <iframe
            className="social-media-embed-facebook social-media-embed-facebook--small"
            src={`https://www.facebook.com/plugins/page.php?href=${encodeURIComponent(facebookPageUrl)}&tabs=${facebookPageTab}&width=240&height=500&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true`}
            scrolling="no"
            frameBorder="0"
            allow="encrypted-media"
            title="Social Media"
          />
          <iframe
            className="social-media-embed-facebook social-media-embed-facebook--large"
            src={`https://www.facebook.com/plugins/page.php?href=${encodeURIComponent(facebookPageUrl)}&tabs=${facebookPageTab}&width=500&height=500&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true`}
            scrolling="no"
            frameBorder="0"
            allow="encrypted-media"
            title="Social Media"
          />
        </>
      }
    </div>
  );
};
