import {FACEBOOK_PAGE_EMBED_TYPE, FACEBOOK_POST_EMBED_TYPE, INSTAGRAM_EMBED_TYPE} from './SocialMediaConstants.js';

export const SocialMediaEmbed = ({
  alignmentClass,
  itemId,
  facebookPageTab,
  embedType,
}) => {
  if (embedType === FACEBOOK_PAGE_EMBED_TYPE) {
    return (
      <div className={`social-media-embed ${alignmentClass ?? ''}`}>
        <iframe
          className="social-media-embed-facebook"
          src={`https://www.facebook.com/plugins/page.php?href=${itemId}&tabs=${facebookPageTab}&width=500&height=500&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true`}
          scrolling="no"
          frameBorder="0"
          allow="encrypted-media"
          title="Social Media"
        />
      </div>
    );
  }

  if (embedType === FACEBOOK_POST_EMBED_TYPE) {
    return (
      <div className={`social-media-embed ${alignmentClass ?? ''}`}>
        <iframe
          className="social-media-embed-facebook"
          src={`https://www.facebook.com/plugins/post.php?href=${itemId}&show_text=true&width=500&height=500`}
          width="500"
          height="500"
          scrolling="no"
          frameBorder="0"
          allow="encrypted-media"
          title="Social Media"
        />
      </div>
    );
  }

  if (embedType === INSTAGRAM_EMBED_TYPE) {
    return (
      <div className={`social-media-embed ${alignmentClass ?? ''}`}>
        <blockquote
          className="instagram-media"
          data-instgrm-captioned
          data-instgrm-permalink={`https://www.instagram.com/reel/${itemId}/?utm_source=ig_embed&utm_campaign=loading`}
          data-instgrm-version="14">
        </blockquote>
      </div>
    );
  }
};
