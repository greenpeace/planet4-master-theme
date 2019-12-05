document.addEventListener( 'DOMContentLoaded', () => {

  function toDataURL( url ) {
    return fetch( url ).then( ( response ) => {
      return response.blob();
    } ).then( blob => {
      return URL.createObjectURL( blob );
    } );
  }

  // Force the download button to always download instead of showing in browser, even cross origin
  document.querySelectorAll( 'a.link-should-download' ).forEach( ( link ) => {
    link.addEventListener( 'click', ( e ) => {
      e.preventDefault();
      let a = document.createElement( 'a' );
      toDataURL( link.href ).then( ( url ) => {
        a.href = url;
        a.download = '';
        document.body.appendChild( a );
        a.click();
        document.body.removeChild( a );
      } );
    } );
  } );
} );
