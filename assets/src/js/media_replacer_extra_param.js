/* global purgedUrls */

// --- Function to modify a simple URL attribute (src, href, value, data-clipboard-text) ---
const updateSimpleAttribute = (el, attrName, purgedUrl, timestamp) => {
  const url = el.getAttribute(attrName);
  if (url && url.includes(purgedUrl) && !url.includes('nocache')) {
    const separator = url.includes('?') ? '&' : '?';
    el.setAttribute(attrName, `${url}${separator}nocache=${timestamp}`);
  }
};

// --- Function to handle the `value` property of input elements ---
const handleInputValue = (el, purgedUrl, timestamp) => {
  if (el.value) {
    const value = el.value;
    if (value.includes(purgedUrl) && !value.includes('nocache')) {
      const separator = value.includes('?') ? '&' : '?';
      el.value = `${value}${separator}nocache=${timestamp}`;
    }
  }
};

// --- Function to andle the `srcset` attribute (for <img> and <source> only) ---
const handleSrcSet = (el, purgedUrl, timestamp) => {
  if (el.tagName === 'IMG' || el.tagName === 'SOURCE') {
    const srcset = el.getAttribute('srcset');
    if (srcset && srcset.includes(purgedUrl)) {
      const updatedSrcset = srcset
        .split(',')
        .map(item => {
          const parts = item.trim().split(/\s+/);
          const url = parts[0];
          const size = parts.slice(1).join(' ');

          if (url && url.includes(purgedUrl) && !url.includes('nocache')) {
            const separator = url.includes('?') ? '&' : '?';
            return `${url}${separator}nocache=${timestamp}${size ? ' ' + size : ''}`;
          }
          return item;
        })
        .join(', ');
      el.setAttribute('srcset', updatedSrcset);
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  if (typeof purgedUrls !== 'undefined' && Array.isArray(purgedUrls) && purgedUrls.length > 0
  ) {
    const timestamp = Date.now();

    const selectorArray = [
      'img',
      'video',
      'audio',
      'source',
      'button',
      'a',
      'link',
      '#sm-attachment-metabox .urlfield',
      '#attachment_url',
    ];

    const selector = selectorArray.join(', ');
    const elements = document.querySelectorAll(selector);

    purgedUrls.forEach(purgedUrl => {
      if (typeof purgedUrl !== 'string' || purgedUrl.length === 0) {
        return;
      }

      elements.forEach(el => {
        // Handle attribute-based URLs
        updateSimpleAttribute(el, 'src', purgedUrl, timestamp);
        updateSimpleAttribute(el, 'href', purgedUrl, timestamp);
        updateSimpleAttribute(el, 'data-clipboard-text', purgedUrl, timestamp);

        // Handle the input value property
        handleInputValue(el, purgedUrl, timestamp);

        // Handle srcset
        handleSrcSet(el, purgedUrl, timestamp);

        // Reload for media elements to apply new src
        if (['VIDEO', 'AUDIO'].includes(el.tagName)) {
          el.load();
        }
      });
    });
  }
});
