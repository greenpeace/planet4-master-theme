const wasRejected = result => 'rejected' === result.status;
const wasFulfilled = result => 'fulfilled' === result.status;
const stateSelectorsRegex = /:(active|focus|visited|hover)/g;

const matchVar = async ( cssVar, target ) => {
  const combinedSelector = cssVar.uniqueSelectors.map( selector => {
    const isBodySelector = !!selector.match( /^body(\.[\w-]*)?$/ );

    // const shouldIncludeStar = !isBodySelector || ['p', 'body'].includes(target.tagName.toLowerCase());
    const shouldIncludeStar = true;

    // return selector;
    return `${ selector }` + (
      shouldIncludeStar
        ? `, ${ selector } *`
        : ''
    );
    // Remove any pseudo selectors that might not match the clicked element right now.
  } ).join().replace( stateSelectorsRegex, '' );


  if ( target.matches( combinedSelector ) ) {
    return [ cssVar ];
  }

  return [];
};

export const getMatchingVars = async ( { cssVars, target } ) => {

  const uniqueVars = cssVars.reduce( ( carry, cssVar ) => {
    if ( !carry.some( collected => collected.name === cssVar.name ) ) {
      carry.push( cssVar );
    }
    return carry;
  }, [] );

  const promises = uniqueVars.map( cssVar => {

    return matchVar( cssVar, target );
  } );

  const results = await Promise.allSettled( promises );

  results.filter( wasRejected ).forEach( console.log );

  const arrays = results.filter( wasFulfilled ).map( result => result.value );

  return [].concat( ...arrays );
};

