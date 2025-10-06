/* eslint-disable no-unused-vars */
/* global turnstile, TurnstileConfig */

/**
 * Handle rendering the Cloudflare Turnstile widget.
 * Reset the widget when the comment reply button is clicked.
 */
document.addEventListener('DOMContentLoaded', () => {
  const turnstileContainer = document.querySelector('#turnstile-container');

  if (!turnstileContainer) {return;}

  // Cloudflare Turnstile callback
  window.onSuccess = token => {
    if (typeof window.turnstileToggleCommentSubmit !== 'function') {
      return;
    }
    window.turnstileToggleCommentSubmit(true);
  };

  // eslint-disable-next-line @wordpress/no-unused-vars-before-return
  const widgetId = turnstile.render('#turnstile-container', {
    sitekey: TurnstileConfig.sitekey,
    callback: window.onSuccess,
    theme: 'light',
  });

  const replyLinks = document.querySelectorAll('.comment-reply-link');

  if (!replyLinks.length) {return;}

  // Let WordPress move the form and reset the widget (clears current state)
  replyLinks.forEach(link => link.addEventListener('click', e => setTimeout(() => turnstile.reset(widgetId), 0)));
});
