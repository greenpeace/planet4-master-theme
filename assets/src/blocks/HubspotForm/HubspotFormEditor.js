import { Fragment, useEffect } from '@wordpress/element';
import { RichText } from '@wordpress/block-editor';
import { Sidebar } from './HubspotFormSidebar';
import { useToAttribute } from './hooks/useToAttribute';
import { useBackgroundImage } from './hooks/useBackgroundImage';
import { getStyleFromClassName } from '../getStyleFromClassName';

const { __ } = wp.i18n;

/**
 * This component is created as a workaround because the <RichText> doesn't provide
 * The functionality to set max characters like any content editable element.
 */
const MaxLengthHelperComponent = ({ value, maxLength, children, darkTheme = false }) => (
  <div>
    { children }
    {maxLength && <span
      className={`max-length-message ${darkTheme ? 'dark-theme' : ''} ${value.length >= maxLength ? 'max-length-error' : ''}`}>
      {value.length >= maxLength
        ? __('Maximum of characters', 'planet4-blocks-backend')
        : `${value.length} / ${maxLength}`}
    </span>}
  </div>
)

export const HubspotFormEditor = ({
  attributes: {
    blockBackgroundImageId,
    blockBackgroundImageUrl,
    blockText = '',
    blockTitle,
    blockStyle,
    ctaText,
    ctaLink,
    ctaNewTab,
    formText = '',
    formTitle,
    hubspotShortcode,
    hubspotThankyouMessage,
    enableCustomHubspotThankyouMessage,
    className,
  },
  setAttributes,
}) => {
  const backgroundImage = useBackgroundImage(blockBackgroundImageUrl);
  const toAttribute = useToAttribute(setAttributes);

  useEffect(() => {
    if(className) {
      setAttributes({ blockStyle: getStyleFromClassName(className) });
    }
  }, [ className ]);

  return (
    <Fragment>
      <Sidebar {...{
        ctaLink,
        ctaNewTab,
        blockBackgroundImageId,
        enableCustomHubspotThankyouMessage,
        hubspotThankyouMessage,
        setAttributes
      }} />
      <section className={`hubspot-form hubspot-form-editor block-wide ${blockStyle}`} style={{...backgroundImage}}>
        <div className='container'>
          <div className='block-wrapper'>
            <div className='block-wrapper-inner block-wrapper-text' style={{...backgroundImage}}>
              <div className='container'>
                <RichText
                  tagName='h1'
                  className='block-title'
                  placeholder={__('Enter title', 'planet4-blocks-backend')}
                  value={blockTitle}
                  onChange={toAttribute('blockTitle')}
                  withoutInteractiveFormatting={true}
                  allowedFormats={[]}
                  multiline='false'
                />
                <MaxLengthHelperComponent value={blockText} maxLength={600} darkTheme={true}>
                  <RichText
                    tagName='p'
                    className='block-text'
                    placeholder={__('Enter description', 'planet4-blocks-backend')}
                    value={blockText}
                    onChange={toAttribute('blockText', 600)}
                    withoutInteractiveFormatting={true}
                    allowedFormats={['core/bold', 'core/italic']}
                  />
                </MaxLengthHelperComponent>
                <RichText
                  tagName='div'
                  className='block-button'
                  placeholder={__('Enter CTA text', 'planet4-blocks-backend')}
                  value={ctaText}
                  onChange={toAttribute('ctaText')}
                  withoutInteractiveFormatting
                  allowedFormats={[]}
                />
              </div>
            </div>
            <div className='block-wrapper-inner block-wrapper-form'>
              <div className='container'>
                <header className='form-header'>
                  <RichText
                    tagName='h1'
                    className='form-title'
                    placeholder={__('Enter form title', 'planet4-blocks-backend')}
                    value={formTitle}
                    onChange={toAttribute('formTitle')}
                    withoutInteractiveFormatting={true}
                    allowedFormats={[]}
                    multiline='false'
                  />
                  <MaxLengthHelperComponent value={formText} maxLength={300}>
                    <RichText
                      tagName='p'
                      className='form-text'
                      placeholder={__('Enter form description', 'planet4-blocks-backend')}
                      value={formText}
                      onChange={toAttribute('formText', 300)}
                      withoutInteractiveFormatting={true}
                      allowedFormats={['core/bold', 'core/italic']}
                    />
                  </MaxLengthHelperComponent>
                </header>
                <div className='form-wrapper-editor'>
                  <div className='form-wrapper-field'>
                    <label>{__('Paste the Hubspot shortcode here', 'planet4-blocks-backend')}</label>
                    <RichText
                      tagName='p'
                      className='hubspot-form-shortcode-editor'
                      placeholder={__('[hubspot type="form" portal="XXXXXX" id="XXXX-XXXX-XXXX-XXXX"]', 'planet4-blocks-backend')}
                      value={hubspotShortcode}
                      onChange={toAttribute('hubspotShortcode')}
                      withoutInteractiveFormatting={true}
                      allowedFormats={[]}
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </Fragment>
  );
};
