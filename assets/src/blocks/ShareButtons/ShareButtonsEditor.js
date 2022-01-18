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

const renderPanelRow = (buttonProps, buttons, setAttributes) => {
  switch (buttonProps.type) {
    case 'whatsapp':
     return <PanelRowWrapper labelText={__('Whatsapp', 'planet4-blocks-backend')}>
        <CheckboxControl
          label={__('Show in menu', 'planet4-blocks-backend')}
          value={buttons.whatsapp.showInMenu}
          checked={buttons.whatsapp.showInMenu}
          onChange={(value) => {
            setAttributes({ buttons: {...buttons, whatsapp: { ...buttons.whatsapp, showInMenu: value } }})
          }}
        />
      </PanelRowWrapper>
    case 'facebook':
     return <PanelRowWrapper labelText={__('Facebook', 'planet4-blocks-backend')}>
        <CheckboxControl
          label={__('Show in menu', 'planet4-blocks-backend')}
          value={buttons.facebook.showInMenu}
          checked={buttons.facebook.showInMenu}
          onChange={(value) => {
            setAttributes({ buttons: {...buttons, facebook: { ...buttons.facebook, showInMenu: value } }})
          }}
        />
      </PanelRowWrapper>
    case 'twitter':
     return <PanelRowWrapper labelText={__('Twitter', 'planet4-blocks-backend')}>
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
      </PanelRowWrapper>
    case 'email':
      return <PanelRowWrapper labelText={__('Email', 'planet4-blocks-backend')}>
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
      </PanelRowWrapper>
    default:
      return null;
  }
};

export const ShareButtonsEditor = ({
  attributes,
  setAttributes,
}) => (
  <>
    <InspectorControls>
      <PanelBody title={__('General Settings', 'planet4-blocks-backend')}>
        <PanelRow>
          <PanelRowWrapper labelText={__('Shared URL', 'planet4-blocks-backend')}>
            <URLInput
              placeholder={__('Enter the shared URL', 'planet4-blocks-backend')}
              value={url}
              onChange={(value) => setAttributes({ url: value })}
            />
            <CheckboxControl
              label={__('Open in a new tab', 'planet4-blocks-backend')}
              value={openInNewTab}
              checked={openInNewTab}
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
              value={utmMedium}
              onChange={(value) => setAttributes({ utmMedium: value })}
            />
            <TextControl
              placeholder={__('Content', 'planet4-blocks-backend')}
              value={utmContent}
              onChange={(value) => setAttributes({ utmContent: value })}
            />
            <TextControl
              placeholder={__('Campaign', 'planet4-blocks-backend')}
              value={utmCampaign}
              onChange={(value) => setAttributes({ utmCampaign: value })}
            />
          </PanelRowWrapper>
        </PanelRow>
        <PanelRow>
          <PanelRowWrapper labelText={__('Google Analytics', 'planet4-blocks-backend')}>
            <TextControl
              placeholder={__('Category ', 'planet4-blocks-backend')}
              value={gaCategory}
              onChange={(value) => setAttributes({ gaCategory: value })}
            />
            <TextControl
              placeholder={__('Action', 'planet4-blocks-backend')}
              value={gaAction}
              onChange={(value) => setAttributes({ gaAction: value })}
            />
            <TextControl
              placeholder={__('Label', 'planet4-blocks-backend')}
              value={gaLabel}
              onChange={(value) => setAttributes({ gaLabel: value })}
            />
          </PanelRowWrapper>
        </PanelRow>
      </PanelBody>
      <PanelBody title={__('Share Buttons', 'planet4-blocks-backend')}>
        {Object.values(buttons).map(
          (buttonProps) => <PanelRow key={buttonProps.type}>
            {renderPanelRow(buttonProps, buttons, setAttributes)}
          </PanelRow>
        )}
      </PanelBody>
    </InspectorControls>
    <div style={{ pointerEvents: 'none' }}>
      <ShareButtonsFrontend {...attributes} />
    </div>
  </>
);
