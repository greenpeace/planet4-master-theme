const wasRejected = result => 'rejected' === result.status;
const wasFulfilled = result => 'fulfilled' === result.status;
const allStateSelectorsRegexp = /:(active|focus|visited|hover|disabled)/g;

const matchVar = async ( cssVar, target ) => {
  const combinedSelector = cssVar.uniqueSelectors.map( selector => {
    const isBodySelector = !!selector.match( /^body(\.[\w-]*)?$/ );

    // Prevent body selector from always showing up, unless a body or paragraph was clicked.
    const shouldIncludeStar = !isBodySelector || ['p', 'body'].includes(target.tagName.toLowerCase());
    // const shouldIncludeStar = true;

    return `${ selector }${ !shouldIncludeStar ? '' : `, ${ selector } *` }`;
    // Remove any pseudo selectors that might not match the clicked element right now.
  } ).filter(selector => !/:(before|after)/.test(selector)).join().replace( allStateSelectorsRegexp, '' );


  if ( target.matches( combinedSelector ) ) {
    return [ cssVar ];
  }

  return [];
};

const excludedVars = [
  '--bs-gutter-x',
  '--bs-gutter-y',
  '--bs-font-sans-serif',
  '--bs-font-monospace',
  '--bs-aspect-ratio',
  '--bs-gradient',
];

export const getMatchingVars = async ( { cssVars, target } ) => {

  const uniqueVars = cssVars.reduce( ( carry, cssVar ) => {
    if (!excludedVars.includes(cssVar.name) && !carry.some(collected => collected.name === cssVar.name)) {
      carry.push( cssVar );
    }
    return carry;
  }, [] );

  const promises = uniqueVars.map( cssVar => {

    return matchVar( cssVar, target );
  } );

  const results = await Promise.allSettled( promises );

  // results.filter( wasRejected ).forEach( console.log );

  const arrays = results.filter( wasFulfilled ).map( result => result.value );

  return [].concat( ...arrays );
};

