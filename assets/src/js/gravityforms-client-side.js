/* global dataLayer */

(() => {
  if (typeof p4GfClientSideConfig === 'undefined') {
    return;
  }

  window.addEventListener('DOMContentLoaded', () => {
    dataLayer.push({
      // eslint-disable-next-line no-undef
      ...p4GfClientSideConfig.formData,
    });
  });

  const urlParams = new URLSearchParams(window.location.search);
  // eslint-disable-next-line no-undef
  p4GfClientSideConfig.populate.forEach(field => {
    const value = urlParams.get(field.parameter);
    const element = document.getElementById(field.fieldId);

    if (element && value !== null) {
      element.value = value;
    }
  });
})();
