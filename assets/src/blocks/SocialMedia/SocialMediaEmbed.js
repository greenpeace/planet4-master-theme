import {FACEBOOK_PAGE_EMBED_TYPE, FACEBOOK_POST_EMBED_TYPE, INSTAGRAM_EMBED_TYPE} from './SocialMediaConstants.js';

export const SocialMediaEmbed = ({
  alignmentClass,
  embedCode,
  facebookPageTab,
  embedType,
}) => {


  if (embedType === FACEBOOK_PAGE_EMBED_TYPE) {
    return (
      <div className={`social-media-embed ${alignmentClass ?? ''}`}>
        <iframe
          className="social-media-embed-facebook social-media-embed-facebook--large"
          src={`https://www.facebook.com/plugins/page.php?href=${embedCode}&tabs=${facebookPageTab}&width=240&height=500&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true`}
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
          src={`https://www.facebook.com/plugins/post.php?href=${embedCode}&show_text=true&width=500`}
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
        <blockquote className="instagram-media"
          data-instgrm-captioned
          data-instgrm-permalink={`https://www.instagram.com/reel/${embedCode}/?utm_source=ig_embed&utm_campaign=loading`}
          data-instgrm-version="14">
        </blockquote>
      </div>
    );
  }
};
