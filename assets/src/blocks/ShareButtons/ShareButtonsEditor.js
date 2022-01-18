import { PanelBody, PanelRow, TextControl, TextareaControl, CheckboxControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { URLInput } from '../../components/URLInput/URLInput';
import { ShareButtonsFrontend } from './ShareButtonsFrontend';

const { __ } = wp.i18n;

const PanelRowWrapper = ({ labelText, helpText, children }) => {
  return (
    <div className='panel-row-wrapper'>
      {labelText && <label className='panel-row-label-text'>{labelText}</label>}
      {helpText && <span className='panel-row-help-text'>{helpText}</span>}
      {children}
    </div>
  )
};

const ButtonPanelRow = ({ buttonProps, buttons, setAttributes }) => {
  return (
    <>
      {
        {
          'whatsapp':
            <PanelRowWrapper labelText={__('Whatsapp', 'planet4-blocks-backend')}>
             <CheckboxControl
               label={__('Show in menu', 'planet4-blocks-backend')}
               value={buttons.whatsapp.showInMenu}
               checked={buttons.whatsapp.showInMenu}
               onChange={(value) => {
                 setAttributes({ buttons: {...buttons, whatsapp: { ...buttons.whatsapp, showInMenu: value } }})
               }}
             />
            </PanelRowWrapper>,
          'facebook':
            <PanelRowWrapper labelText={__('Facebook', 'planet4-blocks-backend')}>
              <CheckboxControl
                label={__('Show in menu', 'planet4-blocks-backend')}
                value={buttons.facebook.showInMenu}
                checked={buttons.facebook.showInMenu}
                onChange={(value) => {
                  setAttributes({ buttons: {...buttons, facebook: { ...buttons.facebook, showInMenu: value } }})
                }}
              />
            </PanelRowWrapper>,
          'twitter':
            <PanelRowWrapper labelText={__('Twitter', 'planet4-blocks-backend')}>
              <CheckboxControl
                label={__('Show in menu', 'planet4-blocks-backend')}
                value={buttons.twitter.showInMenu}
                checked={buttons.twitter.showInMenu}
                onChange={(value) => {
                  setAttributes({ buttons: {...buttons, twitter: { ...buttons.twitter, showInMenu: value } }})
                }}
              />
              <TextControl
                placeholder={__('Text', 'planet4-blocks-backend')}
                value={buttons.twitter.text}
                onChange={(value) => {
                  setAttributes({ buttons: {...buttons, twitter: { ...buttons.twitter, text: value } }})
                }}
              />
              <TextareaControl
                placeholder={__('Description', 'planet4-blocks-backend')}
                value={buttons.twitter.description}
                onChange={(value) => {
                  setAttributes({ buttons: {...buttons, twitter: { ...buttons.twitter, description: value } }})
                }}
              />
              <TextControl
                placeholder={__('Account', 'planet4-blocks-backend')}
                value={buttons.twitter.account}
                onChange={(value) => {
                  setAttributes({ buttons: {...buttons, twitter: { ...buttons.twitter, account: value } }})
                }}
              />
            </PanelRowWrapper>,
          'email':
            <PanelRowWrapper labelText={__('Email', 'planet4-blocks-backend')}>
              <CheckboxControl
                label={__('Show in menu', 'planet4-blocks-backend')}
                value={buttons.email.showInMenu}
                checked={buttons.email.showInMenu}
                onChange={(value) => {
                  setAttributes({ buttons: {...buttons, email: { ...buttons.email, showInMenu: value } }})
                }}
              />
              <TextControl
                placeholder={__('Title', 'planet4-blocks-backend')}
                value={buttons.email.title}
                onChange={(value) => {
                  setAttributes({ buttons: {...buttons, email: { ...buttons.email, title: value } }})
                }}
              />
              <TextareaControl
                placeholder={__('Body', 'planet4-blocks-backend')}
                value={buttons.email.body}
                onChange={(value) => {
                  setAttributes({ buttons: {...buttons, email: { ...buttons.email, body: value } }})
                }}
              />
            </PanelRowWrapper>,
        }[buttonProps.type]
      }
    </>
  )
};

export const ShareButtonsEditor = ({
  attributes,
  setAttributes,
}) => {
  return (
    <>
      <InspectorControls>
        <PanelBody title={__('General Settings', 'planet4-blocks-backend')}>
          <PanelRow>
            <PanelRowWrapper labelText={__('Shared URL', 'planet4-blocks-backend')}>
              <URLInput
                placeholder={__('Enter the shared URL', 'planet4-blocks-backend')}
                value={attributes.url}
                onChange={(value) => setAttributes({ url: value })}
              />
              <CheckboxControl
                label={__('Open in a new tab', 'planet4-blocks-backend')}
                value={attributes.openInNewTab}
                checked={attributes.openInNewTab}
                onChange={(value) => setAttributes({ openInNewTab: value })}
              />
            </PanelRowWrapper>
          </PanelRow>
        </PanelBody>
      </InspectorControls>
      <InspectorControls>
        <PanelBody title={__('Analytics', 'planet4-blocks-backend')}>
          <PanelRow>
            <PanelRowWrapper labelText={__('UTM', 'planet4-blocks-backend')}>
              <TextControl
                placeholder={__('Medium', 'planet4-blocks-backend')}
                value={attributes.utmMedium}
                onChange={(value) => setAttributes({ utmMedium: value })}
              />
              <TextControl
                placeholder={__('Content', 'planet4-blocks-backend')}
                value={attributes.utmContent}
                onChange={(value) => setAttributes({ utmContent: value })}
              />
              <TextControl
                placeholder={__('Campaign', 'planet4-blocks-backend')}
                value={attributes.utmCampaign}
                onChange={(value) => setAttributes({ utmCampaign: value })}
              />
            </PanelRowWrapper>
          </PanelRow>
          <PanelRow>
            <PanelRowWrapper labelText={__('Google Analytics', 'planet4-blocks-backend')}>
              <TextControl
                placeholder={__('Category ', 'planet4-blocks-backend')}
                value={attributes.gaCategory}
                onChange={(value) => setAttributes({ gaCategory: value })}
              />
              <TextControl
                placeholder={__('Action', 'planet4-blocks-backend')}
                value={attributes.gaAction}
                onChange={(value) => setAttributes({ gaAction: value })}
              />
              <TextControl
                placeholder={__('Label', 'planet4-blocks-backend')}
                value={attributes.gaLabel}
                onChange={(value) => setAttributes({ gaLabel: value })}
              />
            </PanelRowWrapper>
          </PanelRow>
        </PanelBody>
        <PanelBody title={__('Share Buttons', 'planet4-blocks-backend')}>
          {Object.values(attributes.buttons).map(
            (buttonProps) => <PanelRow key={buttonProps.type}>
              <ButtonPanelRow
                buttonProps={buttonProps}
                buttons={attributes.buttons}
                setAttributes={setAttributes}
              />
            </PanelRow>
          )}
        </PanelBody>
      </InspectorControls>
      <div style={{ pointerEvents: 'none' }}>
        <ShareButtonsFrontend {...attributes} />
      </div>
    </>
  )
};
