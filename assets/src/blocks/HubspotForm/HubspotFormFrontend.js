import { useRef, useEffect, useState } from '@wordpress/element';
import { useHubspotForm } from './hooks/useHubspotForm';
import { useBackgroundImage } from './hooks/useBackgroundImage';

const { __ } = wp.i18n;

export const HubspotFormFrontend = ({
  formTitle,
  formText,
  blockBackgroundImageUrl,
  blockTitle,
  blockText,
  blockStyle,
  ctaLink,
  ctaText,
  ctaNewTab,
  hubspotShortcode,
  hubspotThankyouMessage,
  enableCustomHubspotThankyouMessage,
}) => {
  const hubspotFormRef = useRef(null);
  const [ styleClass, setStyleClass ] = useState('');
  const { submitted, submittedMessage } = useHubspotForm(hubspotShortcode, hubspotFormRef);
  const backgroundImage = useBackgroundImage(blockBackgroundImageUrl);

  useEffect(() => {
    if(blockStyle) {
      setStyleClass(blockStyle);
    }
  }, [ blockStyle ]);

  return (
    <section className={`hubspot-form block-wide ${styleClass}`} style={{...backgroundImage}}>
      <div className='container'>
        <div className='block-wrapper'>
          <div className='block-wrapper-inner block-wrapper-text' style={{...backgroundImage}}>
            <div className='container'>
              <h1 className='block-title'>{ blockTitle }</h1>
              {blockText && <p className='block-text' dangerouslySetInnerHTML={{ __html: blockText }} />}
              {(ctaLink && ctaText) && <a
                href={ctaLink}
                className='block-button'
                data-ga-category='Hubspot Forms Block'
                data-ga-action='custom form'
                data-ga-label={ctaLink}
                { ...ctaNewTab && { target: '_blank' } }
                >{ctaText}</a>}
            </div>
          </div>
          <div className={`block-wrapper-inner block-wrapper-form ${submitted ? 'submitted-form' : ''}`}>
            <div className='container'>
              {!submitted && <header className='form-header'>
                <h1 className='form-title'>{formTitle}</h1>
                <p className='form-text'>{formText}</p>
              </header>}
              {!submitted ? <div className='form-wrapper'>
                <div id='hubspot-api-form' ref={hubspotFormRef} />
              </div> : <div className='submitted-message'>
                <svg className='icon-tick' width="24" height="20" viewBox="0 0 24 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2 10.4211L8.4 18L22 2" stroke="#074365" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <span>{ enableCustomHubspotThankyouMessage ? hubspotThankyouMessage : submittedMessage }</span>
              </div>}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
