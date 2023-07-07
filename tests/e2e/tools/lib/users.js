const users = {
  admin: {
    username: process.env.WP_TEST_USERNAME || 'admin',
    password: process.env.WP_TEST_PASSWORD || 'admin',
  },
};

export {users};
