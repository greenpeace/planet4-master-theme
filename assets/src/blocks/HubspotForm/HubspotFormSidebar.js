import { CheckboxControl, PanelBody, PanelRow, Button, TextControl } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck, InspectorControls } from '@wordpress/block-editor';
import { URLInput } from '../../components/URLInput/URLInput';
import { useToAttribute } from './hooks/useToAttribute';

const { __ } = wp.i18n;

const PanelRowWrapper = ({ labelText, helpText, children }) => {
  return (
    <div className='panel-row-wrapper'>
      {labelText && <label className='panel-row-label-text'>{labelText}</label>}
      {helpText && <span className='panel-row-help-text'>{helpText}</span>}
      {children}
    </div>
  )
}

export const Sidebar = ({
  ctaLink,
  ctaNewTab,
  blockBackgroundImageId,
  enableCustomHubspotThankyouMessage,
  hubspotThankyouMessage,
  setAttributes,
}) => {
  const toAttribute = useToAttribute(setAttributes);

  const onSelectImageHandler = ({ id, url }) => {
    if(url && id) {
      setAttributes({
        blockBackgroundImageId: id,
        blockBackgroundImageUrl: url,
      });
    }
  };

  const onRemoveImageHandler = () => {
    setAttributes({
      blockBackgroundImageId: null,
      blockBackgroundImageUrl: null,
    });
  }

  return <InspectorControls>
    <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
      <PanelRow>
        <PanelRowWrapper labelText={__('Call to action', 'planet4-blocks-backend')}>
          <URLInput
            placeholder={__('Enter the button link', 'planet4-blocks-backend')}
            value={ctaLink}
            onChange={toAttribute('ctaLink')}
          />
          <CheckboxControl
            label={__('Open in a new tab', 'planet4-blocks-backend')}
            value={ctaNewTab}
            checked={ctaNewTab}
            onChange={toAttribute('ctaNewTab')}
          />
        </PanelRowWrapper>
      </PanelRow>
      <PanelRow>
        <PanelRowWrapper labelText={__('Background image', 'planet4-blocks-backend')}>
          <MediaUploadCheck>
            <nav className='image-actions-nav'>
              <MediaUpload
                title={__('Select image', 'planet4-blocks-backend')}
                type='image'
                onSelect={onSelectImageHandler}
                value={blockBackgroundImageId}
                allowedTypes={[ 'image' ]}
                render={({ open }) => open && (
                  <div>
                    <Button
                      onClick={open}
                      className='button'
                    >
                      {__('Select', 'planet4-blocks-backend')}
                    </Button>
                  </div>)}
              />
              <Button className='button-remove-image' onClick={onRemoveImageHandler}>Remove</Button>
            </nav>
          </MediaUploadCheck>
        </PanelRowWrapper>
      </PanelRow>
      <PanelRow>
        <PanelRowWrapper
          labelText={__('Custom thank you message', 'planet4-blocks-backend')}
          helpText={__('This functionality overrides the default thank you message set to the Form on Hubspot', 'planet4-blocks-backend')}
        >
          <CheckboxControl
            label={__('Enable custom thank you message', 'planet4-blocks-backend')}
            value={enableCustomHubspotThankyouMessage}
            checked={enableCustomHubspotThankyouMessage}
            onChange={toAttribute('enableCustomHubspotThankyouMessage')}
          />
          {enableCustomHubspotThankyouMessage && <TextControl
            placeholder={__('e.g. Thanks for submitting the form.', 'planet4-blocks-backend')}
            value={hubspotThankyouMessage}
            onChange={toAttribute('hubspotThankyouMessage')}
            disabled={false}
          />}
        </PanelRowWrapper>
      </PanelRow>
    </PanelBody>
  </InspectorControls>
};
