/* global dataLayer */
export const setupCookies = () => {
  window.dataLayer = window.dataLayer || [];
  const ENABLE_ANALYTICAL_COOKIES = window.p4bk_vars.enable_analytical_cookies;
  const ENABLE_GOOGLE_CONSENT_MODE = window.p4bk_vars.enable_google_consent_mode;

  // Possible cookie values
  const ONLY_NECESSARY = '1';
  const ALL_COOKIES = '2';
  const NECESSARY_ANALYTICAL_MARKETING = '4';

  function gtag() {
    dataLayer.push(arguments);
  }

  const updateGoogleConsent = (key, granted) => {
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
  };

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

  const createCookie = (name, value, days) => {
    let date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const secureMode = document.location.protocol === 'http:'
      ? ';SameSite=Lax'
      : ';SameSite=None;Secure';
    document.cookie = encodeURI(name) + '=' + encodeURI(value) + ';domain=.' + document.domain + ';path=/;' + '; expires=' + date.toGMTString() + secureMode;
  };

  const readCookie = name => {
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

  const cookie = readCookie('active_consent_choice');
  const cookiesBox = document.querySelector('#set-cookie');
  const nro = document.body.dataset.nro;

  if (cookie === null) {
    if (cookiesBox) {
      cookiesBox.classList.add('shown');
    }
  } else {
    createCookie('gp_nro', nro, 30);
  }

  const allowAllCookies = () => {
    createCookie('active_consent_choice', '1', 365);
    const newCookieValue = ENABLE_ANALYTICAL_COOKIES ? NECESSARY_ANALYTICAL_MARKETING : ALL_COOKIES;
    createCookie('greenpeace', newCookieValue, 365);

    // Remove the 'no_track' cookie, if user accept the cookies consent.
    createCookie('no_track', '0', -1);

    // Create cookie to store last visited nro.
    createCookie('gp_nro', nro, 30);

    // Grant ad storage and analytics storage if Google Consent Mode is enabled.
    updateGoogleConsent('ad_storage', true);
    if (ENABLE_ANALYTICAL_COOKIES) {
      updateGoogleConsent('analytics_storage', true);
    }

    // DataLayer push event on cookies consent.
    dataLayer.push({
      'event' : 'cookiesConsent'
    });

    cookiesBox.classList.remove('shown');
  };

  const allowAllCookiesButtons = [...document.querySelectorAll('.allow-all-cookies')];
  allowAllCookiesButtons.forEach(allowAllCookiesButton => allowAllCookiesButton.onclick = allowAllCookies);

  const toggleCookiesSettings = () => {
    const cookiesSettings = document.querySelector('.cookies-settings');
    const cookiesIntro = document.querySelector('.cookies-intro');
    cookiesSettings.classList.toggle('d-none');
    cookiesIntro.classList.toggle('d-none');
  };

  const showCookiesSettingsButton = cookiesBox.querySelector('#show-cookies-settings');
  if (showCookiesSettingsButton) {
    showCookiesSettingsButton.onclick = toggleCookiesSettings;
  }

  const closeCookiesSettingsButton = cookiesBox.querySelector('.close-cookies-settings');
  if (closeCookiesSettingsButton) {
    closeCookiesSettingsButton.onclick = toggleCookiesSettings;
  }

  const saveCookiesSettingsButton = document.querySelector('#save-cookies-settings');
  if (saveCookiesSettingsButton) {
    saveCookiesSettingsButton.onclick = () => {
      // Get checked settings
      const analyticalCookiesCheckbox = cookiesBox.querySelector('input[name="analytical_cookies"]');
      const allCookiesCheckbox = cookiesBox.querySelector('input[name="all_cookies"]');

      const analyticalCookiesChecked = analyticalCookiesCheckbox ? analyticalCookiesCheckbox.checked : false;
      const allCookiesChecked = allCookiesCheckbox ? allCookiesCheckbox.checked : false;

      let newCookieValue = allCookiesChecked ? ALL_COOKIES : ONLY_NECESSARY;
      if (ENABLE_ANALYTICAL_COOKIES && analyticalCookiesChecked) {
        newCookieValue = NECESSARY_ANALYTICAL_MARKETING;
      }

      // Update cookie value
      createCookie('greenpeace', newCookieValue, 365);
      createCookie('active_consent_choice', '1', 365);

      // Update ad storage and analytics storage if Google Consent Mode is enabled
      updateGoogleConsent('ad_storage', allCookiesChecked);
      if (ENABLE_ANALYTICAL_COOKIES) {
        updateGoogleConsent('analytics_storage', analyticalCookiesChecked);
      }

      // Hide cookies box
      cookiesBox.classList.remove('shown');
    };
  }

  const rejectAllCookies = () => {
    createCookie('active_consent_choice', '1', 365);
    createCookie('greenpeace', ONLY_NECESSARY, 365);

    // Grant ad storage and analytics storage if Google Consent Mode is enabled.
    updateGoogleConsent('ad_storage', false);
    if (ENABLE_ANALYTICAL_COOKIES) {
      updateGoogleConsent('analytics_storage', false);
    }

    // DataLayer push event on cookies consent.
    dataLayer.push({
      'event' : 'cookiesConsent'
    });

    cookiesBox.classList.remove('shown');
  };

  const rejectAllCookiesButtons = [...cookiesBox.querySelectorAll('.reject-all-cookies')];
  rejectAllCookiesButtons.forEach(rejectAllCookiesButton => rejectAllCookiesButton.onclick = rejectAllCookies);

  const greenpeace = readCookie('greenpeace');
  const no_track = readCookie('no_track');
  // Make the necessary cookies checked by default on user's first visit.
  // Here if the No cookies set(absence of 'greenpeace' & 'no_track' cookies) consider as first visit of user.
  if (ENABLE_ANALYTICAL_COOKIES && greenpeace === null && no_track === null) {
    createCookie('greenpeace', ONLY_NECESSARY, 365);
  }
};
