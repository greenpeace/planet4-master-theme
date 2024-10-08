/* global dataLayer */

window.addEventListener('DOMContentLoaded', () => {
  if (typeof dataLayer === 'undefined') {
    return;
  }

  const dataLayerPush = platform => dataLayer.push({
    event: 'uaevent',
    eventCategory: 'Social Share',
    eventAction: platform,
    eventLabel: '{{ social.link }}',
  });
  window.dataLayerPush = dataLayerPush;

  // If the native share functionality is not available on the device,
  // we don't show the corresponding share button.
  if (!navigator.share) {
    return;
  }

  const nativeShareButtons = document.querySelectorAll('.share-buttons .share-btn.native');
  nativeShareButtons.forEach(nativeShareButton => nativeShareButton.style.display = 'block');

  window.nativeShare = async () => {
    try {
      await navigator.share({
        title: '{{ social.title }}',
        url: '{{ share_url ?? social.link }}',
        text: '{{ share_text ?? social.description }}',
      });
      dataLayerPush('Native');
    } catch (err) {
      // eslint-disable-next-line no-console
      console.log(err);
    }
  };
});
