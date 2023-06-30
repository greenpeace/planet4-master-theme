import {nonce} from './nonce';

async function login(context) {
  // Login to admin using request context.
  const response = await context.request.post('./wp-login.php', {
    failOnStatusCode: true,
    form: {
      log: process.env.WP_TEST_USERNAME || 'admin',
      pwd: process.env.WP_TEST_PASSWORD || 'admin',
    },
  });
  await response.dispose();

  const adminNonce = await nonce(context);

  context.storageState = {nonce: adminNonce};

  return adminNonce;
}

export {login};
