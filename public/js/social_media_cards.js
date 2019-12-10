document.addEventListener( 'DOMContentLoaded', () => {

  document.querySelectorAll( 'a.twitter-share' ).forEach( ( link ) => {
    link.addEventListener( 'click', ( event ) => {

      event.preventDefault();

      let popup = window.open(
        'https://twitter.com/intent/tweet'
        + `?text=${ encodeURIComponent( link.dataset.text ) }`
        + `&url=${ encodeURIComponent( link.dataset.socialUrl ) }`,
        'twitter-popup',
        'height=350,width=600'
      );

      if ( popup.focus ) {
        popup.focus();
      }

      return false;
    } );
  } );

  // function toDataURL( url ) {
  //   return fetch( url )
  //     .then( ( response ) => response.blob() )
  //     .then( ( blob ) => URL.createObjectURL( blob ) );
  // }
  //
  // // Force the download button to always download the file instead of showing it in browser, even cross origin.
  // document.querySelectorAll( 'a.link-should-download' ).forEach( ( link ) => {
  //   link.addEventListener( 'click', ( event ) => {
  //     event.preventDefault();
  //     let a = document.createElement( 'a' );
  //     toDataURL( link.href ).then( ( url ) => {
  //       a.href = url;
  //       a.download = '';
  //       document.body.appendChild( a );
  //       a.click();
  //       document.body.removeChild( a );
  //     } );
  //   } );
  // } );
} );
