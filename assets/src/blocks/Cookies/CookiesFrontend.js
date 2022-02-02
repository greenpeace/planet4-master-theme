import { FrontendRichText } from '../../components/FrontendRichText/FrontendRichText';
import { removeCookie, useCookie, writeCookie } from './useCookie';
import { useState, useEffect } from 'react';
import { CookiesFieldResetButton } from './CookiesFieldResetButton';

const { __ } = wp.i18n;
const CONSENT_COOKIE = 'greenpeace';
const ONLY_NECESSARY = '1';
const ALL_COOKIES = '2';
const NECESSARY_ANALYTICAL = '3';
const NECESSARY_ANALYTICAL_MARKETING = '4';

const dataLayer = window.dataLayer || [];

const COOKIES_DEFAULT_COPY = window.p4bk_vars.cookies_default_copy || {};

function gtag() {
  dataLayer.push(arguments);
};

// Planet4 settings(Planet 4 > Cookies > Enable Analytical Cookies).
const ENABLE_ANALYTICAL_COOKIES = window.p4bk_vars.enable_analytical_cookies;

// Planet4 settings (Planet 4 > Analytics > Enable Google Consent Mode).
const ENABLE_GOOGLE_CONSENT_MODE = window.p4bk_vars.enable_google_consent_mode;

const showCookieNotice = () => {
  // the .cookie-notice element belongs to the P4 Master Theme
  const cookieElement = document.querySelector('#set-cookie');
  if (cookieElement) {
    cookieElement.classList.add('shown');
  }
}

const hideCookieNotice = () => {
  // the .cookie-notice element belongs to the P4 Master Theme
  const cookieElement = document.querySelector('#set-cookie');
  if (cookieElement) {
    cookieElement.classList.remove('shown');
  }
}

export const CookiesFrontend = props => {
  const {
    isSelected,
    title,
    description,
    necessary_cookies_name,
    necessary_cookies_description,
    analytical_cookies_name,
    analytical_cookies_description,
    all_cookies_name,
    all_cookies_description,
    isEditing,
    className,
    toAttribute = () => {},
  } = props;

  // Whether consent was revoked by the user since current page load.
  const [userRevokedAllCookies, setUserRevokedAllCookies] = useState(false);
  const [userRevokedAnalytical, setUserRevokedAnalytical] = useState(false);
  const [consentCookie, setConsentCookie] = useCookie(CONSENT_COOKIE);
  const analyticalCookiesChecked = [NECESSARY_ANALYTICAL, NECESSARY_ANALYTICAL_MARKETING].includes(consentCookie);
  const allCookiesChecked = ALL_COOKIES === consentCookie || NECESSARY_ANALYTICAL_MARKETING === consentCookie;
  const hasConsent = allCookiesChecked || analyticalCookiesChecked;

  const updateNoTrackCookie = () => {
    if (hasConsent) {
      removeCookie('no_track');
      if (ENABLE_ANALYTICAL_COOKIES) {
        if (parseInt(consentCookie) === 4) {
          writeCookie('active_consent_choice', '1');
          hideCookieNotice();
        }
      } else if (parseInt(consentCookie) === 2) {
        writeCookie('active_consent_choice', '1');
        hideCookieNotice();
      }
    }
  };
  useEffect(updateNoTrackCookie, [hasConsent, userRevokedAnalytical]);

  const updateConsent = (key, granted) => {
    if (!ENABLE_GOOGLE_CONSENT_MODE) {
      return;
    }

    gtag('consent', 'update', {
      [key]: granted ? 'granted' : 'denied',
    });
    dataLayer.push({
      'event': 'updateConsent',
      [key]: granted ? 'granted' : 'denied',
    });
  }

  const toggleHubSpotConsent = () => {
    if (!allCookiesChecked && userRevokedAllCookies) {
      const _hsp = window._hsp = window._hsp || [];
      _hsp.push(['revokeCookieConsent']);
    }
  }
  useEffect(toggleHubSpotConsent, [allCookiesChecked, userRevokedAllCookies])

  const updateActiveConsentChoice = () => {
    if (allCookiesChecked || analyticalCookiesChecked) {
      writeCookie('active_consent_choice', '1');
      hideCookieNotice();
    }
  }
  useEffect(updateActiveConsentChoice, [allCookiesChecked, analyticalCookiesChecked]);

  const getFieldValue = fieldName => {
    if (props[fieldName] === undefined) {
      return COOKIES_DEFAULT_COPY[fieldName] || '';
    }
    return props[fieldName];
  }

  return (
    <>
      <section className={`block cookies-block ${className ?? ''}`}>
        {(isEditing || title) &&
        <header>
          <FrontendRichText
            tagName="h2"
            className="page-section-header"
            placeholder={__('Enter title', 'planet4-blocks-backend')}
            value={title}
            onChange={toAttribute('title')}
            withoutInteractiveFormatting
            multiline="false"
            editable={isEditing}
            allowedFormats={[]}
          />
        </header>
        }
        {(isEditing || description) &&
        <FrontendRichText
          tagName="p"
          className="page-section-description"
          placeholder={__('Enter description', 'planet4-blocks-backend')}
          value={description}
          onChange={toAttribute('description')}
          withoutInteractiveFormatting
          editable={isEditing}
          allowedFormats={['core/bold', 'core/italic']}
        />
        }
        {(isEditing || (necessary_cookies_name !== '' && necessary_cookies_description !== '')) &&
          <>
            <div className='d-flex align-items-center'>
              <FrontendRichText
                tagName="span"
                className="custom-control-description"
                placeholder={__('Enter necessary cookies name', 'planet4-blocks-backend')}
                value={getFieldValue('necessary_cookies_name')}
                onChange={toAttribute('necessary_cookies_name')}
                withoutInteractiveFormatting
                multiline="false"
                editable={isEditing}
                allowedFormats={[]}
              />
              <span className="always-enabled">{__('Always enabled', 'planet4-master-theme')}</span>
              {isEditing &&
                <CookiesFieldResetButton
                  fieldName='necessary_cookies_name'
                  currentValue={necessary_cookies_name}
                  toAttribute={toAttribute}
                />
              }
            </div>
            <div className='d-flex align-items-center'>
              <FrontendRichText
                tagName="p"
                className="cookies-checkbox-description"
                placeholder={__('Enter necessary cookies description', 'planet4-blocks-backend')}
                value={getFieldValue('necessary_cookies_description')}
                onChange={toAttribute('necessary_cookies_description')}
                withoutInteractiveFormatting
                editable={isEditing}
                allowedFormats={['core/bold', 'core/italic']}
              />
              {isEditing &&
                <CookiesFieldResetButton
                  fieldName='necessary_cookies_description'
                  currentValue={necessary_cookies_description}
                  toAttribute={toAttribute}
                />
              }
            </div>
          </>
        }
        {(ENABLE_ANALYTICAL_COOKIES && (isEditing || (analytical_cookies_name !== '' && analytical_cookies_description !== ''))) &&
          <>
            <div className='d-flex align-items-center'>
              <label className="custom-control" style={isSelected ? { pointerEvents: 'none' } : null}>
                <input
                  type="checkbox"
                  tabIndex={isSelected ? '-1' : null}
                  name="analytical_cookies"
                  onChange={() => {
                    updateConsent('analytics_storage', !analyticalCookiesChecked);
                    if (analyticalCookiesChecked) {
                      setUserRevokedAnalytical(true);
                      setUserRevokedAllCookies(true);
                      if (allCookiesChecked) {
                        setConsentCookie(ALL_COOKIES);
                      } else {
                        setConsentCookie(ONLY_NECESSARY);
                      }
                    } else {
                      if (allCookiesChecked) {
                        setConsentCookie(NECESSARY_ANALYTICAL_MARKETING);
                      } else {
                        setConsentCookie(NECESSARY_ANALYTICAL);
                      }
                    }
                  } }
                  checked={analyticalCookiesChecked}
                  className="p4-custom-control-input"
                />
                <FrontendRichText
                  tagName="span"
                  className="custom-control-description"
                  placeholder={__('Enter analytical cookies name', 'planet4-blocks-backend')}
                  value={getFieldValue('analytical_cookies_name')}
                  onChange={toAttribute('analytical_cookies_name')}
                  withoutInteractiveFormatting
                  multiline="false"
                  editable={isEditing}
                  allowedFormats={[]}
                />
              </label>
              {isEditing &&
                <CookiesFieldResetButton
                  fieldName='analytical_cookies_name'
                  currentValue={analytical_cookies_name}
                  toAttribute={toAttribute}
                />
              }
            </div>
            <div className='d-flex align-items-center'>
              <FrontendRichText
                tagName="p"
                className="cookies-checkbox-description"
                placeholder={__('Enter analytical cookies description', 'planet4-blocks-backend')}
                value={getFieldValue('analytical_cookies_description')}
                onChange={toAttribute('analytical_cookies_description')}
                withoutInteractiveFormatting
                editable={isEditing}
                allowedFormats={['core/bold', 'core/italic']}
              />
              {isEditing &&
                <CookiesFieldResetButton
                  fieldName='analytical_cookies_description'
                  currentValue={analytical_cookies_description}
                  toAttribute={toAttribute}
                />
              }
            </div>
          </>
        }
        {(isEditing || (all_cookies_name !== '' && all_cookies_description !== '')) &&
          <>
            <div className='d-flex align-items-center'>
              <label className="custom-control" style={isSelected ? { pointerEvents: 'none' } : null}>
                <input
                  type="checkbox"
                  tabIndex={isSelected ? '-1' : null}
                  onChange={ () => {
                    updateConsent('ad_storage', !allCookiesChecked);
                    if (allCookiesChecked) {
                      setUserRevokedAllCookies(true);
                      if (ENABLE_ANALYTICAL_COOKIES && analyticalCookiesChecked) {
                        setConsentCookie(NECESSARY_ANALYTICAL);
                      } else {
                        setConsentCookie(ONLY_NECESSARY);
                      }
                    } else {
                      if (ENABLE_ANALYTICAL_COOKIES && analyticalCookiesChecked) {
                        setConsentCookie(NECESSARY_ANALYTICAL_MARKETING);
                      } else {
                        setConsentCookie(ALL_COOKIES);
                      }
                    }
                  } }
                  name="all_cookies"
                  checked={allCookiesChecked}
                  className="p4-custom-control-input"
                />
                <FrontendRichText
                  tagName="span"
                  className="custom-control-description"
                  placeholder={__('Enter all cookies name', 'planet4-blocks-backend')}
                  value={getFieldValue('all_cookies_name')}
                  onChange={toAttribute('all_cookies_name')}
                  withoutInteractiveFormatting
                  multiline="false"
                  editable={isEditing}
                  allowedFormats={[]}
                />
              </label>
              {isEditing &&
                <CookiesFieldResetButton
                  fieldName='all_cookies_name'
                  currentValue={all_cookies_name}
                  toAttribute={toAttribute}
                />
              }
            </div>
            <div className='d-flex align-items-center'>
              <FrontendRichText
                tagName="p"
                className="cookies-checkbox-description"
                placeholder={__('Enter all cookies description', 'planet4-blocks-backend')}
                value={getFieldValue('all_cookies_description')}
                onChange={toAttribute('all_cookies_description')}
                withoutInteractiveFormatting
                editable={isEditing}
                allowedFormats={['core/bold', 'core/italic']}
              />
              {isEditing &&
                <CookiesFieldResetButton
                  fieldName='all_cookies_description'
                  currentValue={all_cookies_description}
                  toAttribute={toAttribute}
                />
              }
            </div>
          </>
        }
      </section>
    </>
  );
}
