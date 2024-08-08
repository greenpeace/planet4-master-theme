import {MediaElementVideo} from './MediaElementVideo';
import {lacksAttributes} from './MediaBlock';

const {useSelect} = wp.data;
const {MediaPlaceholder, InspectorControls, RichText} = wp.blockEditor;
const {__} = wp.i18n;
const {apiFetch} = wp;
const {debounce} = wp.compose;
const {PanelBody, TextControl} = wp.components;
const {url: {addQueryArgs}} = wp.url;
const {element: {Fragment, useCallback}} = wp.element;

const MediaInspectorOptions = ({attributes, setAttributes}) => {
  const {media_url} = attributes;

  const updateEmbed = async mediaUrl => {
    let embed_html;
    try {
      const embedPreview = await apiFetch({
        path: addQueryArgs('/oembed/1.0/proxy', {url: resolveURL(mediaUrl)}),
      });

      embed_html = embedPreview ? embedPreview.html : null;
    } catch (error) {
      embed_html = null;
    }
    setAttributes({media_url: mediaUrl, embed_html});
  };

  const debouncedMediaURLUpdate = useCallback(debounce(updateEmbed, 300), []);

  const onSelectImage = image => {
    const poster_url = image ? image.url : null;

    setAttributes({
      video_poster_img: image.id,
      poster_url,
    });
  };

  return <InspectorControls>
    <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
      <TextControl
        label={__('Media URL/ID', 'planet4-blocks-backend')}
        placeholder={__('Enter URL', 'planet4-blocks-backend')}
        defaultValue={media_url}
        onChange={debouncedMediaURLUpdate}
        help={__('Can be a YouTube, Vimeo or Soundcloud URL or an mp4, mp3 or wav file URL.', 'planet4-blocks-backend')}
      />

      <MediaPlaceholder
        labels={{title: __('Video poster image [Optional]', 'planet4-blocks-backend'), instructions: __('Applicable for .mp4 video URLs only.', 'planet4-blocks-backend')}}
        icon="format-image"
        onSelect={onSelectImage}
        // eslint-disable-next-line no-console
        onError={() => console.log('Error Selecting Image')}
        accept="image/*"
        allowedTypes={['image']}
      />
    </PanelBody>
  </InspectorControls>
  ;
};

const renderView = (attributes, toAttribute) => {
  const {video_title, description, embed_html, media_url, poster_url} = attributes;

  const VideoComponent = media_url?.endsWith('.mp4') ?
    <MediaElementVideo videoURL={media_url} videoPoster={poster_url} /> :
    <div dangerouslySetInnerHTML={{__html: embed_html || null}} />;

  const ErrorMessage = <div className="block-edit-mode-warning components-notice is-error">
    { __('The video URL could not be parsed.', 'planet4-blocks-backend') }
  </div>;

  return (
    <Fragment>
      <header>
        <RichText
          tagName="h2"
          className="page-section-header"
          placeholder={__('Enter title', 'planet4-blocks-backend')}
          value={video_title}
          onChange={toAttribute('video_title')}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
      </header>
      <RichText
        tagName="p"
        className="page-section-description"
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        value={description}
        onChange={toAttribute('description')}
        withoutInteractiveFormatting
        allowedFormats={['core/bold', 'core/italic']}
      />
      {
        media_url && !media_url?.endsWith('.mp4') && !embed_html ?
          ErrorMessage :
          VideoComponent
      }
    </Fragment>
  );
};

const resolveURL = url => {
  // If it's a Youtube ID, turn it into a Youtube URL
  // Youtube IDs are 11 chars long strings with the given set of chars
  return /^[a-z0-9_-]{11}$/i.test(url) ?
    `https://www.youtube.com/watch?v=${url}` :
    url;
};

const patchLegacyAttributes = attributes => {
  return useSelect(select => {
    if (!lacksAttributes(attributes)) {
      return attributes;
    }
    const url = resolveURL(attributes.media_url);
    const embedPreview = select('core').getEmbedPreview(url);

    const embed_html = embedPreview ? embedPreview.html : null;
    const media = select('core').getMedia(attributes.video_poster_img);

    const poster_url = media ? media.media_details.sizes.large.source_url : null;
    return {
      ...attributes,
      embed_html,
      poster_url,
    };
  });
};

export const MediaEditor = props => {
  const attributes = patchLegacyAttributes(props.attributes);
  const {setAttributes, isSelected} = props;

  const toAttribute = attributeName => value => setAttributes({[attributeName]: value});

  return (
    <div>
      { isSelected && <MediaInspectorOptions attributes={attributes} setAttributes={setAttributes} /> }
      {
        renderView(attributes, toAttribute)
      }
    </div>
  );
};
