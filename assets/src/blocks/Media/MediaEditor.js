import { Fragment, useCallback } from "@wordpress/element";
import { PanelBody } from '@wordpress/components';
import { MediaPlaceholder, InspectorControls } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { debounce } from 'lodash';

import { MediaEmbedPreview } from "./MediaEmbedPreview";
import { MediaElementVideo } from './MediaElementVideo';

const { __ } = wp.i18n;
const {apiFetch} = wp;
const {addQueryArgs} = wp.url;
const { RichText } = wp.blockEditor;


const MediaInspectorOptions = ({ attributes, setAttributes }) => {
  const { media_url } = attributes;

  const updateEmbed = async media_url => {
    let embed_html;
    try {
      const embedPreview = await apiFetch({
        path: addQueryArgs('/oembed/1.0/proxy', { url: resolveURL(media_url) }),
      });

      embed_html = embedPreview ? embedPreview.html : null;
    } catch (error) {
      embed_html = null;
    }
    setAttributes({ media_url, embed_html });
  };

  const debouncedMediaURLUpdate = useCallback(debounce(updateEmbed, 300), []);

  function onSelectImage(image) {
    const poster_url = image ? image.sizes.large.url : null;

    setAttributes({
      video_poster_img: image.id,
      poster_url,
    });
  }

  return <InspectorControls>
      <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
        <TextControl
          label={__('Media URL/ID', 'planet4-blocks-backend')}
          placeholder={__('Enter URL', 'planet4-blocks-backend')}
          defaultValue={ media_url }
          onChange={ debouncedMediaURLUpdate }
          help={__('Can be a YouTube, Vimeo or Soundcloud URL or an mp4, mp3 or wav file URL.', 'planet4-blocks-backend')}
        />

        <MediaPlaceholder
          labels={{ title: __('Video poster image [Optional]', 'planet4-blocks-backend'), instructions: __('Applicable for .mp4 video URLs only.', 'planet4-blocks-backend')}}
          icon="format-image"
          onSelect={ onSelectImage }
          onError={ console.log }
          accept="image/*"
          allowedTypes={["image"]}
        />
      </PanelBody>
    </InspectorControls>
  ;
}

const renderView = (props, toAttribute) => {
  const { attributes } = props;
  const { video_title, description, embed_html, media_url, poster_url } = attributes;

  const VideoComponent = media_url?.endsWith('.mp4')
    ? <MediaElementVideo videoURL={ media_url } videoPoster={ poster_url } />
    : <MediaEmbedPreview html={ embed_html || null } />;

  const ErrorMessage = <div className="block-edit-mode-warning components-notice is-error">
    { __( 'The video URL could not be parsed.', 'planet4-blocks-backend' ) }
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
          keepPlaceholderOnFocus={true}
          withoutInteractiveFormatting
          characterLimit={40}
          multiline="false"
        />
      </header>
      <RichText
        tagName="p"
        className="page-section-description"
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        value={description}
        onChange={toAttribute('description')}
        keepPlaceholderOnFocus={true}
        withoutInteractiveFormatting
        characterLimit={200}
      />
      {
        media_url && !media_url?.endsWith('.mp4') && !embed_html
          ? ErrorMessage
          : VideoComponent
      }
    </Fragment>
  )
}

const resolveURL = url => {
  // If it's a Youtube ID, turn it into a Youtube URL
  // Youtube IDs are 11 chars long strings with the given set of chars
  return /^[a-z0-9_-]{11}$/i.test(url)
    ? `https://www.youtube.com/watch?v=${url}`
    : url;
}

export const MediaEditor = (props) => {
  const { setAttributes, isSelected } = props;

  const toAttribute = attributeName => value => setAttributes({ [attributeName]: value });

  return (
    <div>
      { isSelected && <MediaInspectorOptions { ...props } /> }
      {
        renderView(props, toAttribute)
      }
    </div>
  );
}
