import {SocialMediaEmbed} from './SocialMediaEmbed';

export const SocialMediaFrontend = ({
  title,
  description,
  embed_code,
  social_media_url,
  facebook_page_tab,
  alignment_class,
  className,
  embed_type,
  animation,
}) => {
  const cssClasses = `block social-media-block ${className ?? ''} ${animation ? `animate__animated ${animation}` : ''}`;

  return (
    <section className={cssClasses}>
      {!!title &&
      <header>
        <h2 className="page-section-header">{title}</h2>
      </header>
      }
      {!!description &&
      <p className="page-section-description" dangerouslySetInnerHTML={{__html: description}} />
      }
      <SocialMediaEmbed
        embedCode={embed_code}
        facebookPageTab={facebook_page_tab}
        facebookPageUrl={social_media_url}
        alignmentClass={alignment_class}
        embedType={embed_type}
      />
    </section>
  );
};
