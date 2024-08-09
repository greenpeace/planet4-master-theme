import {FrontendRichText} from '../components/FrontendRichText/FrontendRichText';
import {removeCookie, useCookie, writeCookie} from './useCookie';
import {CookiesFieldResetButton} from './CookiesFieldResetButton';

const {__} = wp.i18n;
const {useState, useEffect} = wp.element;

const dataLayer = window.dataLayer || [];

const COOKIES_DEFAULT_COPY = window.p4_vars.options.cookies_default_copy || {};

function gtag() {
  dataLayer.push(arguments);
}

// Planet4 settings(Planet 4 > Cookies > Enable Analytical Cookies).
const ENABLE_ANALYTICAL_COOKIES = window.p4_vars.options.enable_analytical_cookies;

// Planet4 settings (Planet 4 > Analytics > Enable Google Consent Mode).
const ENABLE_GOOGLE_CONSENT_MODE = window.p4_vars.options.enable_google_consent_mode;

const CONSENT_COOKIE = 'greenpeace';
const NO_TRACK_COOKIE = 'no_track';
const ACTIVE_CONSENT_COOKIE = 'active_consent_choice';
const ONLY_NECESSARY = '1';
const NECESSARY_MARKETING = '2';
const NECESSARY_ANALYTICAL = '3';
const NECESSARY_ANALYTICAL_MARKETING = '4';

const hideCookiesBox = () => {
  // the .cookie-notice element belongs to the P4 Master Theme
  const cookiesBox = document.querySelector('#set-cookie');
  if (cookiesBox) {
    cookiesBox.classList.remove('shown');
  }
};

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
  const [userRevokedMarketingCookies, setUserRevokedMarketingCookies] = useState(false);
  const [userRevokedAnalyticalCookies, setUserRevokedAnalyticalCookies] = useState(false);
  const [consentCookie, setConsentCookie] = useCookie(CONSENT_COOKIE);
  const analyticalCookiesChecked = [NECESSARY_ANALYTICAL, NECESSARY_ANALYTICAL_MARKETING].includes(consentCookie);
  const marketingCookiesChecked = [NECESSARY_MARKETING, NECESSARY_ANALYTICAL_MARKETING].includes(consentCookie);
  const hasConsent = marketingCookiesChecked || analyticalCookiesChecked;

  const updateNoTrackCookie = () => {
    if (hasConsent) {
      removeCookie(NO_TRACK_COOKIE);
    } else {
      writeCookie(NO_TRACK_COOKIE, '1');
    }
  };
  useEffect(updateNoTrackCookie, [userRevokedAnalyticalCookies, userRevokedMarketingCookies]);

  const updateConsent = (key, granted) => {
    if (!ENABLE_GOOGLE_CONSENT_MODE) {
      return;
    }

    gtag('consent', 'update', {
      [key]: granted ? 'granted' : 'denied',
    });

    // eslint-disable-next-line no-undef
    const updatedCapabilities = {...capabilities, [key]: granted ? 'granted' : 'denied'};
    dataLayer.push({
      event: 'updateConsent',
      ...updatedCapabilities,
    });

    let ad_storage = true;
    if (key === 'ad_storage' && granted) {
      ad_storage = false;
    } else if (key !== 'ad_storage') {
      // eslint-disable-next-line no-undef
      ad_storage = capabilities.ad_storage === 'denied';
    }
    gtag('set', 'ads_data_redaction', ad_storage);
  };

  const toggleHubSpotConsent = () => {
    if (!marketingCookiesChecked && userRevokedMarketingCookies) {
      const _hsp = window._hsp = window._hsp || [];
      _hsp.push(['revokeCookieConsent']);
    }
  };
  useEffect(toggleHubSpotConsent, [marketingCookiesChecked, userRevokedMarketingCookies]);

  const updateActiveConsentChoice = () => {
    if (hasConsent) {
      writeCookie(ACTIVE_CONSENT_COOKIE, '1');
      hideCookiesBox();
    }
  };
  useEffect(updateActiveConsentChoice, [marketingCookiesChecked, analyticalCookiesChecked]);

  const getFieldValue = fieldName => {
    if (props[fieldName] === undefined) {
      return COOKIES_DEFAULT_COPY[fieldName] || '';
    }
    return props[fieldName] || '';
  };

  const isFieldValid = fieldName => getFieldValue(fieldName).trim().length > 0;

  return (
    <>
      <section className={`block cookies-block ${className ?? ''}`}>
        {(isEditing || title) &&
        <header>
          <FrontendRichText
            tagName="h2"
            className="page-section-header cookies-title"
            placeholder={__('Enter title', 'planet4-blocks-backend')}
            value={title}
            onChange={toAttribute('title')}
            withoutInteractiveFormatting
            editable={isEditing}
            allowedFormats={[]}
          />
        </header>
        }
        {(isEditing || description) &&
        <FrontendRichText
          tagName="p"
          className="page-section-description cookies-description"
          placeholder={__('Enter description', 'planet4-blocks-backend')}
          value={description}
          onChange={toAttribute('description')}
          withoutInteractiveFormatting
          editable={isEditing}
          allowedFormats={['core/bold', 'core/italic']}
        />
        }
        {(isEditing || (isFieldValid('necessary_cookies_name') && isFieldValid('necessary_cookies_description'))) &&
          <>
            <div className="d-flex align-items-center">
              <FrontendRichText
                tagName="span"
                className="custom-control-description cookies-header-text"
                placeholder={__('Enter necessary cookies name', 'planet4-blocks-backend')}
                value={getFieldValue('necessary_cookies_name')}
                onChange={toAttribute('necessary_cookies_name')}
                withoutInteractiveFormatting
                editable={isEditing}
                allowedFormats={[]}
              />
              <span className="always-enabled">{__('Always enabled', 'planet4-blocks')}</span>
              {isEditing &&
                <CookiesFieldResetButton
                  fieldName="necessary_cookies_name"
                  currentValue={necessary_cookies_name}
                  toAttribute={toAttribute}
                />
              }
            </div>
            <div className="d-flex align-items-center">
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
                  fieldName="necessary_cookies_description"
                  currentValue={necessary_cookies_description}
                  toAttribute={toAttribute}
                />
              }
            </div>
          </>
        }
        {(ENABLE_ANALYTICAL_COOKIES && (isEditing || (isFieldValid('analytical_cookies_name') && isFieldValid('analytical_cookies_description')))) &&
          <>
            <div className="d-flex align-items-center">
              {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
              <label className="custom-control" style={isSelected ? {pointerEvents: 'none'} : null} htmlFor="analytical-cookies__control">
                <input
                  id="analytical-cookies__control"
                  type="checkbox"
                  tabIndex={isSelected ? '-1' : null}
                  name="analytical_cookies"
                  onChange={() => {
                    updateConsent('analytics_storage', !analyticalCookiesChecked);
                    if (analyticalCookiesChecked) {
                      setUserRevokedAnalyticalCookies(true);
                      if (marketingCookiesChecked) {
                        setConsentCookie(NECESSARY_MARKETING);
                      } else {
                        setConsentCookie(ONLY_NECESSARY);
                      }
                    } else if (marketingCookiesChecked) {
                      setConsentCookie(NECESSARY_ANALYTICAL_MARKETING);
                    } else {
                      setConsentCookie(NECESSARY_ANALYTICAL);
                    }
                  }}
                  checked={analyticalCookiesChecked}
                  className="p4-custom-control-input"
                />
                <FrontendRichText
                  tagName="span"
                  className="custom-control-description cookies-header-text"
                  placeholder={__('Enter analytical cookies name', 'planet4-blocks-backend')}
                  value={getFieldValue('analytical_cookies_name')}
                  onChange={toAttribute('analytical_cookies_name')}
                  withoutInteractiveFormatting
                  editable={isEditing}
                  allowedFormats={[]}
                />
              </label>
              {isEditing &&
                <CookiesFieldResetButton
                  fieldName="analytical_cookies_name"
                  currentValue={analytical_cookies_name}
                  toAttribute={toAttribute}
                />
              }
            </div>
            <div className="d-flex align-items-center">
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
                  fieldName="analytical_cookies_description"
                  currentValue={analytical_cookies_description}
                  toAttribute={toAttribute}
                />
              }
            </div>
          </>
        }
        {(isEditing || (isFieldValid('all_cookies_name') && isFieldValid('all_cookies_description'))) &&
          <>
            <div className="d-flex align-items-center">
              {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
              <label className="custom-control" style={isSelected ? {pointerEvents: 'none'} : null} htmlFor="all-cookies__control">
                <input
                  id="all-cookies__control"
                  type="checkbox"
                  tabIndex={isSelected ? '-1' : null}
                  onChange={() => {
                    updateConsent('ad_storage', !marketingCookiesChecked);
                    if (marketingCookiesChecked) {
                      setUserRevokedMarketingCookies(true);
                      if (ENABLE_ANALYTICAL_COOKIES && analyticalCookiesChecked) {
                        setConsentCookie(NECESSARY_ANALYTICAL);
                      } else {
                        setConsentCookie(ONLY_NECESSARY);
                      }
                    } else if (ENABLE_ANALYTICAL_COOKIES && analyticalCookiesChecked) {
                      setConsentCookie(NECESSARY_ANALYTICAL_MARKETING);
                    } else {
                      setConsentCookie(NECESSARY_MARKETING);
                    }
                  }}
                  name="all_cookies"
                  checked={marketingCookiesChecked}
                  className="p4-custom-control-input"
                />
                <FrontendRichText
                  tagName="span"
                  className="custom-control-description cookies-header-text"
                  placeholder={__('Enter all cookies name', 'planet4-blocks-backend')}
                  value={getFieldValue('all_cookies_name')}
                  onChange={toAttribute('all_cookies_name')}
                  withoutInteractiveFormatting
                  editable={isEditing}
                  allowedFormats={[]}
                />
              </label>
              {isEditing &&
                <CookiesFieldResetButton
                  fieldName="all_cookies_name"
                  currentValue={all_cookies_name}
                  toAttribute={toAttribute}
                />
              }
            </div>
            <div className="d-flex align-items-center">
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
                  fieldName="all_cookies_description"
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
};
