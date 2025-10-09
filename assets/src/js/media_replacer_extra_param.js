/* global purgedUrls */

document.addEventListener('DOMContentLoaded', () => {
  purgedUrls.forEach(purgedUrl => {
    const selector = 'img, video, audio, iframe, embed';
    const timestamp = Date.now();

    document.querySelectorAll(selector).forEach(el => {
      const src = el.getAttribute('src');

      // --- Handle the `src` attribute ---
      if (src && src.includes(purgedUrl) && !src.includes('nocache')) {
        const separator = src.includes('?') ? '&' : '?';
        el.setAttribute('src', `${src}${separator}nocache=${timestamp}`);
      }

      // --- Handle the `srcset` attribute (for <img> only) ---
      const srcset = el.getAttribute('srcset');
      if (srcset && srcset.includes(purgedUrl)) {
        const updatedSrcset = srcset
          .split(',')
          .map(item => {
            const [url, size] = item.trim().split(/\s+/, 2);
            if (!url.includes('nocache')) {
              const separator = url.includes('?') ? '&' : '?';
              return `${url}${separator}nocache=${timestamp}${size ? ' ' + size : ''}`;
            }
            return item;
          })
          .join(', ');
        el.setAttribute('srcset', updatedSrcset);
      }

      // --- Reload for media elements to apply new src ---
      if (['VIDEO', 'AUDIO'].includes(el.tagName)) {
        el.load();
      }
    });
  });
});
