/**
 * Returns a new AbortController in case of being supported by the Browser
 * Through this method you could abort any request in case of it's needed.
 *
 * @returns {AbortController | undefined}
 */
export const getAbortController = () => (
  typeof AbortController === 'undefined' ? undefined : new AbortController()
);
