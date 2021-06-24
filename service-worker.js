addEventListener('fetch', event => {
  if (event.request.url.match(/wp-admin/) || event.request.url.match(/preview=true/)) {
    return;
  }

  console.log('Service worker is working');
});
