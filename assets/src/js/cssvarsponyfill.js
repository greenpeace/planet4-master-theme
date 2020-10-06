/* global cssVars */
export const setupCSSVarsPonyfill = () => {
  if ('undefined' !== typeof cssVars) {
    cssVars();
  }
};
