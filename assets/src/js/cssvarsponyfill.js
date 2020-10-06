/* global cssVars */
export const setupCSSVarsPonyfill = () => {
  // Taken from https://stackoverflow.com/questions/36653217/opera-mini-browser-detection-using-javascript.
  const isOperaMini = Object.prototype.toString.call(window.operamini) === '[object OperaMini]';

  if (isOperaMini) {
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/css-vars-ponyfill/2.3.1/css-vars-ponyfill.min.js';
    script.onload = () => cssVars();
    document.head.append(script);

    return;
  }

  if ('undefined' !== typeof cssVars) {
    cssVars();
  }
};
