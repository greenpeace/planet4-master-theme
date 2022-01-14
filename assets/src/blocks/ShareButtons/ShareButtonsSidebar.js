import { PanelBody, PanelRow, TextControl, TextareaControl, CheckboxControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { URLInput } from '../../components/URLInput/URLInput';

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
  url,
  openInNewTab,
  utmMedium,
  utmContent,
  utmCampaign,
  gaCategory,
  gaAction,
  gaLabel,
  whatsapp,
  facebook,
  twitter,
  email,
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
        <PanelBody title={__('Whatsapp Share', 'planet4-blocks-backend')}>
          <PanelRow>
            <PanelRowWrapper labelText={__('Settings', 'planet4-blocks-backend')}>
              <CheckboxControl
                label={__('Show in menu', 'planet4-blocks-backend')}
                value={whatsapp.showInMenu}
                checked={whatsapp.showInMenu}
                onChange={(value) => {
                  setAttributes({ whatsapp: {...whatsapp, showInMenu: value} })
                }}
              />
            </PanelRowWrapper>
          </PanelRow>
        </PanelBody>
        <PanelBody title={__('Facebook Share', 'planet4-blocks-backend')}>
          <PanelRow>
            <PanelRowWrapper labelText={__('Settings', 'planet4-blocks-backend')}>
              <CheckboxControl
                label={__('Show in menu', 'planet4-blocks-backend')}
                value={facebook.showInMenu}
                checked={facebook.showInMenu}
                onChange={(value) => {
                  setAttributes({ facebook: {...facebook, showInMenu: value} })
                }}
              />
            </PanelRowWrapper>
          </PanelRow>
        </PanelBody>
        <PanelBody title={__('Twitter Share', 'planet4-blocks-backend')}>
          <PanelRow>
            <PanelRowWrapper labelText={__('Settings', 'planet4-blocks-backend')}>
              <CheckboxControl
                label={__('Show in menu', 'planet4-blocks-backend')}
                value={twitter.showInMenu}
                checked={twitter.showInMenu}
                onChange={(value) => {
                  setAttributes({ twitter: {...twitter, showInMenu: value} })
                }}
              />
              <TextControl
                placeholder={__('Text', 'planet4-blocks-backend')}
                value={twitter.text}
                onChange={(value) => {
                  setAttributes({ twitter: {...twitter, text: value} })
                }}
              />
              <TextareaControl
                placeholder={__('Description', 'planet4-blocks-backend')}
                value={twitter.description}
                onChange={(value) => {
                  setAttributes({ twitter: {...twitter, description: value} })
                }}
              />
              <TextControl
                placeholder={__('Account', 'planet4-blocks-backend')}
                value={twitter.account}
                onChange={(value) => {
                  setAttributes({ twitter: {...twitter, account: value} })
                }}
              />
            </PanelRowWrapper>
          </PanelRow>
        </PanelBody>
        <PanelBody title={__('Email Share', 'planet4-blocks-backend')}>
          <PanelRow>
            <PanelRowWrapper labelText={__('Settings', 'planet4-blocks-backend')}>
              <CheckboxControl
                label={__('Show in menu', 'planet4-blocks-backend')}
                value={email.showInMenu}
                checked={email.showInMenu}
                onChange={(value) => { setAttributes({ email: {...email, showInMenu: value} }) }}
              />
              <TextControl
                placeholder={__('Title', 'planet4-blocks-backend')}
                value={email.title}
                onChange={(value) => { setAttributes({ email: {...email, title: value} }) }}
              />
              <TextareaControl
                placeholder={__('Body', 'planet4-blocks-backend')}
                value={email.body}
                onChange={(value) => { setAttributes({ email: {...email, body: value} }) }}
              />
            </PanelRowWrapper>
          </PanelRow>
        </PanelBody>
      </InspectorControls>
    </>
  )
};
