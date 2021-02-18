/* global dataLayer */
export const setupCookies = () => {
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
      c = ca[i];
      while (c.charAt(0) === ' ') {
        c = c.substring(1, c.length);
      }
      if (c.indexOf(nameEQ) === 0) {
        return c.substring(nameEQ.length, c.length);
      }
    }
    return null;
  };

  const cookie = window.readCookie('greenpeace');
  const cookieElement = document.querySelector('#set-cookie');
  const nro = document.body.dataset.nro;

  if (cookie == null) {
    cookieElement.classList.add('shown');
  } else {
    window.createCookie('gp_nro', nro, 30);
  }

  const hideCookieButton = document.querySelector('#hidecookie');
  if (hideCookieButton) {
    hideCookieButton.onclick = () => {
      window.createCookie('greenpeace', '2', 365);

      // Remove the 'no_track' cookie, if user accept the cookies consent.
      window.createCookie('no_track', '0', -1);

      // Create cookie to store last visited nro.
      window.createCookie('gp_nro', nro, 30);

      // DataLayer push event on cookies consent.
      window.dataLayer = window.dataLayer || [];
      dataLayer.push({
        'event' : 'cookiesConsent'
      });

      cookieElement.classList.remove('shown');
    };
  }
};
