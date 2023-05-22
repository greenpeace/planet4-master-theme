import {nonce} from './nonce';

async function rest(context, options) {
  const {path, ...fetchOptions} = options;

  try {
    const response = await context.request.fetch(path, {
      ...fetchOptions,
      failOnStatusCode: false,
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': context.storageState.nonce,
        ...(fetchOptions.headers || {}),
      },
    });
    const json = await response.json();

    if (!response.ok()) {
      throw json;
    }

    return json;
  } catch (error) {
    // Nonce in invalid, retry again with a renewed nonce.
    if (
      typeof error === 'object' &&
      error !== null &&
      Object.prototype.hasOwnProperty.call(error, 'code') &&
      error.code === 'rest_cookie_invalid_nonce'
    ) {
      await nonce(context);

      return rest(options);
    }

    throw error;
  }
}

export {rest};
