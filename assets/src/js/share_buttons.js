/* global dataLayer, shareButtonsData */

document.addEventListener('DOMContentLoaded', () => {
  if(!window.dataLayerPush) {
    window.dataLayerPush = platform => {
      const shared = {
        eventCategory: 'Social Share',
        eventAction: platform,
        eventLabel: shareButtonsData.link,
      };

      dataLayer.push({event: 'uaevent', ...shared});
      dataLayer.push({event: 'page_shared', channel: platform, ...shared});
    };
  }

  // If the native share functionality is not available on the device,
  // we don't show the corresponding share button.
  if (navigator.share) {
    const nativeShareButtons = document.querySelectorAll('.share-buttons .share-btn.native');
    nativeShareButtons.forEach(nativeShareButton => nativeShareButton.style.display = 'block');

    window.nativeShare = async () => {
      try {
        await navigator.share({
          title: shareButtonsData.title,
          url: shareButtonsData.share_url ?? shareButtonsData.link,
          text: shareButtonsData.share_text ?? shareButtonsData.description,
        });
        dataLayerPush('Native');
      } catch (err) {
        // eslint-disable-next-line no-console
        console.log(err);
      }
    };
  }
});
