/**
 * Initializes a global VWO (Visual Website Optimizer) queue function and ensures visibility for VWO elements.
 *
 * @return {{vwoCss: Function}} An object with a `vwoCss()` method stub.
 */

window.vwo_$ = window.vwo_$ || function() {
  (window._vwoQueue = window._vwoQueue || []).push(arguments);
  return {
    vwoCss() {},
  };
};

// eslint-disable-next-line no-undef
vwo_$('body').vwoCss({'visibility':'visible !important'});
