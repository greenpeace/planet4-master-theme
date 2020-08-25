import { Fragment, useEffect, useState, useCallback } from "@wordpress/element";
import { PanelBody } from '@wordpress/components';
import { MediaPlaceholder, InspectorControls } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { debounce } from 'lodash';

import { MediaEmbedPreview } from "./MediaEmbedPreview";
import { MediaElementVideo } from './MediaElementVideo';

const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;

const MediaInspectorOptions = ({ attributes, setAttributes }) => {
  // Using a state to prevent the input losing the cursor position,
  // a React issue reported multiple times, see:
  // https://github.com/facebook/react/issues/14904
  // https://github.com/facebook/react/issues/955#issuecomment-469352730
  const [ media_url, setMediaURL ] = useState(attributes.media_url);
  const debouncedMediaURLUpdate = useCallback(debounce(value => setAttributes({ media_url: value }), 300), []);

  function onSelectImage({id}) {
    setAttributes({video_poster_img: id});
  }

  return <InspectorControls>
      <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
        <TextControl
          label={__('Media URL/ID', 'planet4-blocks-backend')}
          placeholder={__('Enter URL', 'planet4-blocks-backend')}
          value={ media_url }
          onChange={
            value => {
              setMediaURL(value)
              debouncedMediaURLUpdate(value)
            }
          }
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
  const { attributes, setAttributes, isSelected } = props;
  const { media_url, video_poster_img } = attributes;

  const toAttribute = attributeName => value => setAttributes({ [attributeName]: value });

  const embed_html = useSelect(select => {
    const result = resolveURL(media_url);
    const embedPreview = select('core').getEmbedPreview(result);

    return embedPreview ? embedPreview.html : null;
  }, [media_url]);

  const poster_url = useSelect(select => {
    const media = select('core').getMedia(video_poster_img);

    return media ? media.media_details.sizes.large.source_url : null;
  }, [video_poster_img]);

  // In order to render the block statically with no endpoint calls
  // we need to store the embed's HTML and the poster image URL.
  // As that content is fetched when the Media URL or the image ID changes,
  // the effect itself is more or less a change listener for embedHTML and poster_url,
  // which are retrieved in the previous useSelect call right before this effect.
  // Once we know the content of embedHTML and poster_url, we store them as attributes
  // for the block to render statically.
  useEffect(() => {
    setAttributes({
      embed_html: embed_html || null,
      poster_url: poster_url || null
    })
  },
  [ embed_html, poster_url ]);

  return (
    <div>
      { isSelected && <MediaInspectorOptions { ...props } /> }
      {
        renderView(props, toAttribute)
      }
    </div>
  );
}
