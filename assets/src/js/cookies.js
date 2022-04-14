/* global dataLayer */
export const setupCookies = () => {
  window.dataLayer = window.dataLayer || [];
  const ENABLE_ANALYTICAL_COOKIES = window.p4bk_vars.enable_analytical_cookies;
  const ENABLE_GOOGLE_CONSENT_MODE = window.p4bk_vars.enable_google_consent_mode;

  // Possible cookie values
  const ONLY_NECESSARY = '1';
  const NECESSARY_MARKETING = '2';
  const NECESSARY_ANALYTICAL = '3';
  const NECESSARY_ANALYTICAL_MARKETING = '4';

  const ALL_COOKIES = ENABLE_ANALYTICAL_COOKIES ? NECESSARY_ANALYTICAL_MARKETING : NECESSARY_MARKETING;

  function gtag() {
    dataLayer.push(arguments);
  }

  const updateGoogleConsent = (capabilities, event = 'update') => {
    gtag('consent', event, capabilities);

    dataLayer.push({
      event: event + 'Consent',
      ...capabilities,
    });
  };

  const defaultGoogleConsent = () => {
    updateGoogleConsent(
      {
        ad_storage: 'denied',
        ...ENABLE_ANALYTICAL_COOKIES && {'analytics_storage': 'denied'},
      },
      'default'
    );
  };

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

  const removeCookie = name => createCookie(name, '0', -1);

  const cookie = readCookie('active_consent_choice');
  const cookiesBox = document.querySelector('#set-cookie');
  const currentNRO = document.body.dataset.nro;
  const previousNRO = readCookie('gp_nro');
  const greenpeace = readCookie('greenpeace');
  const noTrack = readCookie('no_track');

  // If Google Consent Mode is enabled,
  // set default ad storage and analytics storage to 'denied' as first action on every page until consent is given.
  // If consent given, update consent on every page.
  if (ENABLE_GOOGLE_CONSENT_MODE) {
    const marketing_consent = noTrack === null
      && cookie !== null
      && [NECESSARY_MARKETING, NECESSARY_ANALYTICAL_MARKETING].includes(greenpeace);

    if (marketing_consent) {
      // If user consents, update on every page.
      updateGoogleConsent({
        'ad_storage': 'granted',
        ...ENABLE_ANALYTICAL_COOKIES && {'analytics_storage': 'granted'}
      });
    } else {
      // If user has not, default to denied on every page.
      defaultGoogleConsent();
    }
  }

  const showCookiesBox = () => {
    if (cookiesBox) {
      cookiesBox.classList.add('shown');
    }
  };

  const hideCookiesBox = () => {
    if (cookiesBox) {
      cookiesBox.classList.remove('shown');
    }
  };

  // Make the necessary cookies checked by default on user's first visit.
  // Here if the No cookies set (absence of 'greenpeace' & 'no_track' cookies) consider as first visit of user.
  if (greenpeace === null && noTrack === null) {
    createCookie('greenpeace', ONLY_NECESSARY, 365);
  }

  // Create/update cookie to store last visited NRO.
  if (previousNRO !== currentNRO) {
    createCookie('gp_nro', currentNRO, 30);
  }

  if (cookie === null) {
    showCookiesBox();
  }

  const allowAllCookies = () => {
    createCookie('active_consent_choice', '1', 365);
    createCookie('greenpeace', ALL_COOKIES, 365);
    removeCookie('no_track');

    // Grant ad storage and analytics storage if Google Consent Mode is enabled.
    if (ENABLE_GOOGLE_CONSENT_MODE) {
      updateGoogleConsent({
        'ad_storage': 'granted',
        ...ENABLE_ANALYTICAL_COOKIES && {'analytics_storage': 'granted'}
      });
    }

    // DataLayer push event on cookies consent.
    dataLayer.push({
      'event' : 'cookiesConsent'
    });

    hideCookiesBox();
  };

  const allowAllCookiesButtons = [...document.querySelectorAll('.allow-all-cookies')];
  allowAllCookiesButtons.forEach(allowAllCookiesButton => allowAllCookiesButton.onclick = allowAllCookies);

  const toggleCookiesSettings = () => {
    const cookiesSettings = document.querySelector('.cookies-settings');
    const cookiesIntro = document.querySelector('.cookies-intro');
    cookiesSettings.classList.toggle('d-none');
    cookiesIntro.classList.toggle('d-none');
  };

  const showCookiesSettingsButton = document.querySelector('#show-cookies-settings');
  if (showCookiesSettingsButton) {
    showCookiesSettingsButton.onclick = toggleCookiesSettings;
  }

  const closeCookiesSettingsButton = document.querySelector('.close-cookies-settings');
  if (closeCookiesSettingsButton) {
    closeCookiesSettingsButton.onclick = toggleCookiesSettings;
  }

  // Save cookies settings functionality
  const saveCookiesSettingsButton = document.querySelector('#save-cookies-settings');
  if (saveCookiesSettingsButton) {
    saveCookiesSettingsButton.onclick = () => {
      // Get checked settings
      const analyticalCookiesCheckbox = cookiesBox.querySelector('input[name="analytical_cookies"]');
      const marketingCookiesCheckbox = cookiesBox.querySelector('input[name="all_cookies"]');

      const analyticalCookiesChecked = analyticalCookiesCheckbox ? analyticalCookiesCheckbox.checked : false;
      const marketingCookiesChecked = marketingCookiesCheckbox ? marketingCookiesCheckbox.checked : false;

      let newCookieValue = marketingCookiesChecked ? NECESSARY_MARKETING : ONLY_NECESSARY;
      if (ENABLE_ANALYTICAL_COOKIES && analyticalCookiesChecked) {
        newCookieValue = marketingCookiesChecked ? NECESSARY_ANALYTICAL_MARKETING : NECESSARY_ANALYTICAL;
      }

      // Update cookie value and save active consent choice
      createCookie('greenpeace', newCookieValue, 365);
      createCookie('active_consent_choice', '1', 365);

      // Update no track cookie
      if (newCookieValue !== ONLY_NECESSARY) {
        removeCookie('no_track');
      } else {
        createCookie('no_track', '1', 365);
      }

      // Update ad storage and analytics storage if Google Consent Mode is enabled
      if (ENABLE_GOOGLE_CONSENT_MODE) {
        updateGoogleConsent({
          'ad_storage': marketingCookiesChecked ? 'granted' : 'denied',
          ...ENABLE_ANALYTICAL_COOKIES && {'analytics_storage': analyticalCookiesChecked ? 'granted' : 'denied'}
        });
      }

      dataLayer.push({
        'event' : 'updateConsent'
      });

      hideCookiesBox();
    };
  }

  const rejectAllCookies = () => {
    createCookie('greenpeace', ONLY_NECESSARY, 365);
    createCookie('active_consent_choice', '1', 365);
    createCookie('no_track', '1', 365);

    // Deny ad storage and analytics storage if Google Consent Mode is enabled.
    if (ENABLE_GOOGLE_CONSENT_MODE) {
      updateGoogleConsent({
        'ad_storage': 'denied',
        ...ENABLE_ANALYTICAL_COOKIES && {'analytics_storage': 'denied'}
      });
    }

    dataLayer.push({
      'event' : 'updateConsent'
    });

    hideCookiesBox();
  };

  const rejectAllCookiesButtons = [...document.querySelectorAll('.reject-all-cookies')];
  rejectAllCookiesButtons.forEach(rejectAllCookiesButton => rejectAllCookiesButton.onclick = rejectAllCookies);
};
