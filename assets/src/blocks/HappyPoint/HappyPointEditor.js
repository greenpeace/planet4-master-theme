import {HappyPointFrontend} from './HappyPointFrontend';
import {USE_EMBED_CODE, USE_IFRAME_URL, USE_NONE} from './HappyPointConstants';
import {OverrideFormHelp} from './OverrideFormHelp';

const {useSelect} = wp.data;
const {InspectorControls, BlockControls, MediaUpload, MediaUploadCheck} = wp.blockEditor;
const {debounce} = wp.compose;
const {useState} = wp.element;
const {
  TextControl,
  TextareaControl,
  FocalPointPicker,
  RadioControl,
  CheckboxControl,
  RangeControl,
  PanelBody,
  Button,
  ToolbarGroup,
  ToolbarButton,
} = wp.components;
const {__} = wp.i18n;

export const HappyPointEditor = ({attributes, setAttributes, isSelected}) => {
  const {
    focus_image,
    opacity,
    id,
    iframe_url,
    embed_code,
    override_default_content,
    local_content_provider,
  } = attributes;
  const [iframeUrl, setIframeUrl] = useState(iframe_url || '');
  const [embedCode, setEmbedCode] = useState(embed_code || '');
  const dimensions = {width: 400, height: 100};

  const {imageUrl} = useSelect(select => {
    // eslint-disable-next-line no-shadow
    let imageUrl = '';
    if (id && id > 0) {
      const imageDetails = select('core').getMedia(id);
      imageUrl = (imageDetails && imageDetails.source_url) || '';
    }
    return {imageUrl};
  }, [id]);

  let focal_point_params = {x: '', y: ''};

  if (focus_image) {
    const focus_image_str = focus_image.replace(/%/g, '');
    const [x, y] = focus_image_str.split(' ');
    focal_point_params = {x: x / 100, y: y / 100};
  } else {
    focal_point_params = {x: 0.5, y: 0.5};
  }

  const getImageOrButton = openEvent => {
    if (id && 0 < id) {
      return <HappyPointFrontend {...attributes} />;
    } else if (isSelected) {
      return (
        <div style={{marginBottom: 10}}>
          <Button
            onClick={openEvent}
            className="button">
            + {__('Select Background Image', 'planet4-blocks-backend')}
          </Button>
        </div>
      );
    }
    return null;
  };

  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value,
  });

  const onFocalPointChange = ({x, y}) => {
    const floatX = parseFloat(x).toFixed(2);
    const floatY = parseFloat(y).toFixed(2);
    setAttributes({focus_image: `${floatX * 100}% ${floatY * 100}%`});
  };

  const onRemoveImages = () => setAttributes({id: '', focus_image: ''});

  // eslint-disable-next-line no-shadow
  const selectImage = ({id}) => setAttributes({id});

  const debounceIframeUrl = debounce(url => {
    setAttributes({iframe_url: url});
  }, 300);

  const debounceEmbedCode = debounce(code => {
    setAttributes({embed_code: code});
  }, 300);

  return (
    <>
      {isSelected && (
        <div>
          <InspectorControls>
            <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
              <div className="wp-block-master-theme-happypoint__RangeControl">
                <RangeControl
                  __next40pxDefaultSize
                  __nextHasNoMarginBottom
                  label={__('Opacity', 'planet4-blocks-backend')}
                  value={opacity}
                  onChange={toAttribute('opacity')}
                  min={1}
                  max={100}
                  initialPosition={opacity}
                  help={__('We use an overlay to fade the image back. Use a number between 1 and 100, the higher the number, the more faded the image will look. If you leave this empty, the default of 30 will be used.', 'planet4-blocks-backend')}
                />
              </div>
              <CheckboxControl
                __nextHasNoMarginBottom
                label={__('Override default form', 'planet4-blocks-backend')}
                value={override_default_content}
                checked={override_default_content}
                onChange={checked => {
                  setAttributes({
                    override_default_content: checked,
                    embed_code: checked ? embedCode : null,
                  });
                }}
              />
              <OverrideFormHelp />
              {override_default_content &&
                <>
                  <div>
                    <RadioControl
                      label="Form type"
                      selected={local_content_provider}
                      options={[
                        {label: 'None', value: USE_NONE},
                        {label: 'URL (for iframe)', value: USE_IFRAME_URL},
                        {label: 'Embed code', value: USE_EMBED_CODE},
                      ]}
                      onChange={toAttribute('local_content_provider')}
                    />
                  </div>
                  {USE_IFRAME_URL === local_content_provider && (
                    <div>
                      <TextControl
                        __nextHasNoMarginBottom
                        __next40pxDefaultSize
                        label={__('Iframe URL', 'planet4-blocks-backend')}
                        placeholder={__('Enter iframe URL', 'planet4-blocks-backend')}
                        value={iframeUrl}
                        onChange={url => {
                          setIframeUrl(url);
                          debounceIframeUrl(url);
                        }}
                        help={__(
                          'By default this block uses the "Happy Point Subscribe Form URL" in Planet 4 Settings - Default content. ' +
                          'If a URL is set here, it will override this setting.',
                          'planet4-blocks-backend'
                        )}
                      />
                    </div>
                  )}
                  {USE_EMBED_CODE === local_content_provider && (
                    <div>
                      <TextareaControl
                        __nextHasNoMarginBottom
                        label={__('HubSpot embed code', 'planet4-blocks-backend')}
                        value={embedCode}
                        onChange={code => {
                          setEmbedCode(code);
                          debounceEmbedCode(code);
                        }}
                        help={__(
                          'By default this block uses the "Happy Point HubSpot embed code" in Planet 4 Settings - Default content. ' +
                          'If an embed code is set here, it will override this setting.',
                          'planet4-blocks-backend'
                        )}
                      />
                    </div>
                  )}
                </>
              }
              {id && 0 < id &&
                <div className="wp-block-master-theme-happypoint__FocalPointPicker">
                  <strong className="components-base-control__help">
                    {__('Select image focal point', 'planet4-blocks-backend')}
                  </strong>
                  <p className="components-base-control__help">
                    {__('Drag the mouse to the focal area or input the position with numbers for more precision.', 'planet4-blocks-backend')}
                  </p>
                  <FocalPointPicker
                    __nextHasNoMarginBottom
                    url={imageUrl}
                    dimensions={dimensions}
                    value={focal_point_params}
                    onChange={onFocalPointChange}
                    help={__(
                      'Drag "left" to move across the horizontal axis and slide "top" upwards to move through the vertical axis.',
                      'planet4-blocks-backend'
                    )}
                  />
                </div>
              }
            </PanelBody>
            <PanelBody title={__('Learn more about this block ', 'planet4-blocks-backend')} initialOpen={false}>
              <p className="components-base-control__help">
                <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/happy-point/" rel="noreferrer">
                  P4 Handbook Happy Point
                </a>
                {' '} &#128588;
              </p>
            </PanelBody>
          </InspectorControls>
          <BlockControls>
            {id && 0 < id && (
              <ToolbarGroup>
                <MediaUploadCheck>
                  <MediaUpload
                    onSelect={selectImage}
                    allowedTypes={['image']}
                    value={id}
                    type="image"
                    render={({open}) => (
                      <ToolbarButton
                        className="components-icon-button components-toolbar__control"
                        label={__('Edit Image', 'planet4-blocks-backend')}
                        onClick={open}
                        icon="edit"
                      />
                    )}
                  />
                </MediaUploadCheck>
                <ToolbarButton
                  className="components-icon-button components-toolbar__control"
                  label={__('Remove Image', 'planet4-blocks-backend')}
                  onClick={onRemoveImages}
                  icon="trash"
                />
              </ToolbarGroup>
            )}
          </BlockControls>
        </div>
      )}
      <MediaUploadCheck>
        <MediaUpload
          title={__('Select Background Image', 'planet4-blocks-backend')}
          type="image"
          onSelect={selectImage}
          value={id}
          allowedTypes={['image']}
          render={({open}) => getImageOrButton(open)}
        />
      </MediaUploadCheck>
      {(!id || id < 0) && <HappyPointFrontend {...attributes} />}
    </>
  );
};
