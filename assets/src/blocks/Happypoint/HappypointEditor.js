import { Fragment } from '@wordpress/element';
import { HappypointFrontend } from './HappypointFrontend';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';

import { debounce } from 'lodash';

const {
  InspectorControls,
  BlockControls,
  MediaUpload,
  MediaUploadCheck
} = wp.blockEditor;

const { __ } = wp.i18n;

import {
  TextControl,
  FocalPointPicker,
  ToggleControl,
  RangeControl,
  PanelBody,
  Button,
  Toolbar,
  IconButton
} from '@wordpress/components';

export const HappypointEditor = ({ attributes, setAttributes, isSelected }) => {
  const { focus_image, opacity, mailing_list_iframe, id, iframe_url } = attributes;
  const [iframeUrl, setIframeUrl] = useState(iframe_url || '');
  const dimensions = { width: 400, height: 100 };

  const { imageUrl } = useSelect(select => {
    let imageUrl = '';
    if (id && id > 0) {
      const imageDetails = select('core').getMedia(id);
      imageUrl = (imageDetails && imageDetails.source_url) || '';
    }
    return { imageUrl };
  }, [ id ]);

  let focal_point_params = { x: '', y: '' };

  if (focus_image) {
    let focus_image_str = focus_image.replace(/%/g, '');
    let [x, y] = focus_image_str.split(' ');
    focal_point_params = { x: x / 100, y: y / 100 };
  } else {
    focal_point_params = { x: 0.5, y: 0.5 };
  }

  const getImageOrButton = openEvent => {
    if (id && 0 < id) {
      return <HappypointFrontend {...attributes} />;
    } else if (isSelected) {
      return (
        <div style={{ marginBottom: 10 }}>
          <Button
            onClick={openEvent}
            className='button'>
            + {__('Select Background Image', 'planet4-blocks-backend')}
          </Button>
        </div>
      );
    }
    return null;
  };

  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value
  });

  const onFocalPointChange = ({ x, y }) => {
    const floatX = parseFloat(x).toFixed(2);
    const floatY = parseFloat(y).toFixed(2);
    setAttributes({ focus_image: `${floatX * 100}% ${floatY * 100}%` });
  }

  const onRemoveImages = () => setAttributes({ id: -1, focus_image: '' });

  const selectImage = ({ id }) => setAttributes({ id });

  const debounceIframeUrl = debounce(url => {
    setAttributes( { iframe_url: url } );
  }, 300);

  return (
    <Fragment>
      {isSelected && (
        <div>
          <InspectorControls>
            <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
              <div className="wp-block-master-theme-happypoint__RangeControl">
                <RangeControl
                  label={__('Opacity', 'planet4-blocks-backend')}
                  value={opacity}
                  onChange={toAttribute('opacity')}
                  min={1}
                  max={100}
                  initialPosition={opacity}
                  help={__('We use an overlay to fade the image back. Use a number between 1 and 100, the higher the number, the more faded the image will look. If you leave this empty, the default of 30 will be used.', 'planet4-blocks-backend')}
                />
              </div>
              <ToggleControl
                label={__('Use mailing list iframe', 'planet4-blocks-backend')}
                help={__('Use mailing list iframe', 'planet4-blocks-backend')}
                value={mailing_list_iframe}
                checked={mailing_list_iframe}
                onChange={toAttribute('mailing_list_iframe')}
              />
              <TextControl
                label={__('Iframe url', 'planet4-blocks-backend')}
                placeholder={__('Enter Iframe url', 'planet4-blocks-backend')}
                help={__('If a url is set in this field and the \'mailing list iframe\' option is enabled, it will override the planet4 engaging network setting.', 'planet4-blocks-backend')}
                value={iframeUrl}
                onChange={url => {
                  setIframeUrl(url);
                  debounceIframeUrl(url);
                }}
              />
              {id && 0 < id &&
                <div className="wp-block-master-theme-happypoint__FocalPointPicker">
                  <FocalPointPicker
                    url={imageUrl}
                    dimensions={dimensions}
                    value={focal_point_params}
                    onChange={onFocalPointChange}
                    label={__('Select focus point for background image', 'planet4-blocks-backend')}
                  />
                </div>
              }
            </PanelBody>
          </InspectorControls>
          <BlockControls>
            {id && 0 < id && (
              <Toolbar>
                <MediaUploadCheck>
                  <MediaUpload
                    onSelect={selectImage}
                    allowedTypes={['image']}
                    value={id}
                    type='image'
                    render={({ open }) => {
                      return (
                        <IconButton
                          className='components-icon-button components-toolbar__control'
                          label={__('Edit Image', 'planet4-blocks-backend')}
                          onClick={open}
                          icon='edit'
                        />
                      );
                    }}
                  />
                </MediaUploadCheck>
                <IconButton
                  className='components-icon-button components-toolbar__control'
                  label={__('Remove Image', 'planet4-blocks-backend')}
                  onClick={onRemoveImages}
                  icon='trash'
                />
              </Toolbar>
            )}
          </BlockControls>
        </div>
      )}
      <MediaUploadCheck>
        <MediaUpload
          title={__('Select Background Image', 'planet4-blocks-backend')}
          type='image'
          onSelect={selectImage}
          value={id}
          allowedTypes={['image']}
          render={({ open }) => getImageOrButton(open)}
        />
      </MediaUploadCheck>
      {(!id || id < 0) && <HappypointFrontend {...attributes} />}
    </Fragment>
  );
}
