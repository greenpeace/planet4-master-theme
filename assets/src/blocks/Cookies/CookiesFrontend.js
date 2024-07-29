import {removeCookie, useCookie, writeCookie} from './useCookie';

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
  const {title, description, className} = props;

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

    let updatedCapabilities = {[key]: granted ? 'granted' : 'denied'};
    if (typeof capabilities !== 'undefined') {
      // eslint-disable-next-line no-undef
      updatedCapabilities = {...capabilities, [key]: granted ? 'granted' : 'denied'};
    }

    dataLayer.push({
      event: 'updateConsent',
      ...updatedCapabilities,
    });

    let ad_storage = true;
    if (key === 'ad_storage' && granted) {
      ad_storage = false;
    } else if (key !== 'ad_storage' && typeof capabilities !== 'undefined') {
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
        {title &&
          <header>
            <h2 className="page-section-header cookies-title">{title}</h2>
          </header>
        }
        {description &&
          <p
            className="page-section-description cookies-description"
            dangerouslySetInnerHTML={{__html: description}}
          />
        }
        {isFieldValid('necessary_cookies_name') && isFieldValid('necessary_cookies_description') &&
          <>
            <div className="d-flex align-items-center">
              <span className="custom-control-description cookies-header-text">
                {getFieldValue('necessary_cookies_name')}
              </span>
              <span className="always-enabled">{__('Always enabled', 'planet4-blocks')}</span>
            </div>
            <p className="cookies-checkbox-description">
              {getFieldValue('necessary_cookies_description')}
            </p>
          </>
        }
        {ENABLE_ANALYTICAL_COOKIES && isFieldValid('analytical_cookies_name') && isFieldValid('analytical_cookies_description') &&
          <>
            <div className="d-flex align-items-center">
              {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
              <label className="custom-control" style={isSelected ? {pointerEvents: 'none'} : null} htmlFor="analytical-cookies__control">
                <input
                  id="analytical-cookies__control"
                  type="checkbox"
                  name="analytical_cookies"
                  checked={analyticalCookiesChecked}
                  className="p4-custom-control-input"
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
                />
                <span className="custom-control-description cookies-header-text">
                  {getFieldValue('analytical_cookies_name')}
                </span>
              </label>
            </div>
            <p className="cookies-checkbox-description">
              {getFieldValue('analytical_cookies_description')}
            </p>
          </>
        }
        {isFieldValid('all_cookies_name') && isFieldValid('all_cookies_description') &&
          <>
            <div className="d-flex align-items-center">
              {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
              <label className="custom-control" style={isSelected ? {pointerEvents: 'none'} : null} htmlFor="all-cookies__control">
                <input
                  id="all-cookies__control"
                  type="checkbox"
                  name="all_cookies"
                  checked={marketingCookiesChecked}
                  className="p4-custom-control-input"
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
                />
                <span className="custom-control-description cookies-header-text">
                  {getFieldValue('all_cookies_name')}
                </span>
              </label>
            </div>
            <p className="cookies-checkbox-description">
              {getFieldValue('all_cookies_description')}
            </p>
          </>
        }
      </section>
    </>
  );
};
