import {MediaElementVideo} from './MediaElementVideo';

const wrapEmbedHTML = embed_html => {
  const wrapperDiv = document.createElement('div');
  wrapperDiv.innerHTML = embed_html;
  if (wrapperDiv.firstChild?.src?.includes('youtube')) {
    wrapperDiv.firstChild.src = wrapperDiv.firstChild.src.replace('youtube.com', 'youtube-nocookie.com');
  }

  wrapperDiv.className = 'embed-container';
  if (wrapperDiv.firstChild?.removeAttribute) {
    wrapperDiv.firstChild.removeAttribute('width');
    wrapperDiv.firstChild.removeAttribute('height');
  }

  return wrapperDiv.outerHTML;
};

export const MediaFrontend = attributes => {
  const {
    video_title,
    description,
    embed_html,
    poster_url,
    media_url,
    className,
  } = attributes;

  if (!media_url) {
    return '';
  }

  return (
    <section className={`block media-block ${className ?? ''}`}>
      {
        video_title &&
        <header>
          <h2 className="page-section-header">{ video_title }</h2>
        </header>
      }
      {
        description &&
        <p className="page-section-description" dangerouslySetInnerHTML={{__html: description}} />
      }
      {
        media_url && media_url.endsWith('.mp4') ?
          <MediaElementVideo videoURL={media_url} videoPoster={poster_url} /> :
          <div dangerouslySetInnerHTML={{__html: wrapEmbedHTML(embed_html) || null}} />
      }
    </section>
  );
};
