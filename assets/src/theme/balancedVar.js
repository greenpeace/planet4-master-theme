// Copied from https://github.com/greenpeace/planet4-postcss-css-variables/blob/06a7d101f9d4714a9a85d4829cdab3f233096f28/lib/balanced-var.js
const balanced = require( 'balanced-match' );

// Check for balanced `var(` and `)` pairs inside `value`, and return the 3 fragments:
// `body` (inside), `pre` (before), `post` (after) of the found wrapper
function balancedVar(value) {
  const match = balanced( '(', ')', value );
  if ( !match ) {
    return;
  }
  if ( /(?:^|[^\w-])var$/.test( match.pre ) ) {
    // Remove the var from the end of pre
    return {
      pre: match.pre.slice( 0, -3 ),
      body: match.body,
      post: match.post
    };
  }
  // Check inside body
  const bodyMatch = balancedVar( match.body );
  if ( bodyMatch ) {
    // Reconstruct pre and post
    return {
      pre: match.pre + '(' + bodyMatch.pre,
      body: bodyMatch.body,
      post: bodyMatch.post + ')' + match.post
    };
  }
  // Check inside post
  const postMatch = balancedVar( match.post );

  if ( postMatch ) {
    // Reconstruct pre
    return {
      pre: match.pre + '(' + match.body + ')' + postMatch.pre,
      body: postMatch.body,
      post: postMatch.post
    };
  }
}

module.exports = balancedVar;
