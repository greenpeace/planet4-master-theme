import {SocialMediaEmbed} from './SocialMediaEmbed';

export const SocialMediaFrontend = ({
  title,
  description,
  embed_code,
  facebook_page_tab,
  alignment_class,
  className,
  embed_type,
}) => (
  <section className={`block social-media-block ${className ?? ''}`}>
    {!!title &&
      <header>
        <h2 className="page-section-header">{title}</h2>
      </header>
    }
    {!!description &&
      <p className="page-section-description" dangerouslySetInnerHTML={{__html: description}} />
    }
    <SocialMediaEmbed
      itemId={embed_code}
      facebookPageTab={facebook_page_tab}
      alignmentClass={alignment_class}
      embedType={embed_type}
    />
  </section>
);
