/**
 * Function with a similar signature as WordPress's apiFetch, but doesn't do a bunch of things we don't need and cause
 * issues. You could as well use what is inside this function directly, but having this in a single function makes it
 * easier to use in a ternary assignment, as in the editor it still needs to use apiFetch in the same place.
 *
 * 1) It will pass a nonce, which is on the page but could have been cached and expired. Even when an endpoint
 * doesn't require a nonce, passing an invalid one will make it fail. Passing nonces on public endpoints is pretty
 * pointless regardless.
 *
 * 2) It doesn't use the correct language for the API when using WPML. An alternative is to use what we already have in
 * document.body.dataset.nro, which has the language suffix, so will cause the API to use that language.
 *
 * 3) It's yet another blocking script to load.
 *
 * @param {string} url
 */
export const fetchJson = async url => {
  const response = await fetch(url);
  return response.json();
};
