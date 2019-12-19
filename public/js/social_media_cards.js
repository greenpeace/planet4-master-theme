document.addEventListener( 'DOMContentLoaded', () => {

  function openPopup(url) {
    let popup = window.open(
      url,
      'popup',
      'height=350,width=600'
    );

    if ( popup.focus ) {
      popup.focus();
    }
  }

  document.querySelectorAll( 'a.twitter-share' ).forEach( ( link ) => {
    link.addEventListener( 'click', ( event ) => {
      event.preventDefault();
      openPopup(
        `https://twitter.com/intent/tweet?text=${ encodeURIComponent( link.dataset.text ) }&url=${ encodeURIComponent( link.dataset.socialUrl ) }`
      );

      return false;
    } );
  } );

  document.querySelectorAll( 'a.facebook-share' ).forEach( ( link ) => {
    link.addEventListener('click', ( event ) => {
      event.preventDefault();

      openPopup( link.href );

      return false;
    });
  });
} );
