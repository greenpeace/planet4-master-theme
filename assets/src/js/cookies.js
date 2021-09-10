/* global dataLayer */
export const setupCookies = () => {
  window.dataLayer = window.dataLayer || [];
  const ENABLE_ANALYTICAL_COOKIES = window.p4bk_vars.enable_analytical_cookies;
  const ENABLE_GOOGLE_CONSENT_MODE = window.p4bk_vars.enable_google_consent_mode;

  function gtag() {
    dataLayer.push(arguments);
  }

  // If Google Consent Mode is enabled, set default ad storage and analytics storage to 'denied' if needed
  if (ENABLE_GOOGLE_CONSENT_MODE) {
    const defaultCookieConsentNeeded = !document.cookie.includes('greenpeace=') && !document.cookie.includes('no_track');

    if (defaultCookieConsentNeeded) {
      gtag('consent', 'default', {
        'ad_storage': 'denied',
        ...ENABLE_ANALYTICAL_COOKIES && { 'analytics_storage': 'denied' },
      });
      dataLayer.push({
        'event' : 'defaultConsent',
        'ad_storage': 'denied',
        ...ENABLE_ANALYTICAL_COOKIES && { 'analytics_storage': 'denied' },
      });
    }
  }

  window.createCookie = (name, value, days) => {
    let date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const secureMode = document.location.protocol === 'http:'
      ? ';SameSite=Lax'
      : ';SameSite=None;Secure';
    document.cookie = encodeURI(name) + '=' + encodeURI(value) + ';domain=.' + document.domain + ';path=/;' + '; expires=' + date.toGMTString() + secureMode;
  };

  window.readCookie = name => {
    const nameEQ = name + '=';
    const ca = document.cookie.split(';');
    let c;
    for (let i = 0; i < ca.length; i++) {
      c = ca[i].trimStart();
      if (c.indexOf(nameEQ) === 0) {
        return c.substring(nameEQ.length, c.length);
      }
    }
    return null;
  };

  const cookie = window.readCookie('greenpeace');
  const cookieElement = document.querySelector('#set-cookie');
  const nro = document.body.dataset.nro;

  if (cookie === null) {
    if (cookieElement) {
      cookieElement.classList.add('shown');
    }
  } else {
    window.createCookie('gp_nro', nro, 30);
  }

  const hideCookieButton = document.querySelector('#hidecookie');
  if (hideCookieButton) {
    hideCookieButton.onclick = () => {
      const newCookieValue = ENABLE_ANALYTICAL_COOKIES ? '4' : '2';
      window.createCookie('greenpeace', newCookieValue, 365);

      // Remove the 'no_track' cookie, if user accept the cookies consent.
      window.createCookie('no_track', '0', -1);

      // Create cookie to store last visited nro.
      window.createCookie('gp_nro', nro, 30);

      // Grant ad storage and analytics storage if Google Consent Mode is enabled.
      if (ENABLE_GOOGLE_CONSENT_MODE) {
        gtag('consent', 'update', {
          'ad_storage': 'granted',
          ...ENABLE_ANALYTICAL_COOKIES && { 'analytics_storage': 'granted' },
        });
        dataLayer.push({
          'event' : 'updateConsent',
          'ad_storage': 'granted',
          ...ENABLE_ANALYTICAL_COOKIES && { 'analytics_storage': 'granted' },
        });
      }

      // DataLayer push event on cookies consent.
      dataLayer.push({
        'event' : 'cookiesConsent'
      });

      cookieElement.classList.remove('shown');
    };
  }
};
