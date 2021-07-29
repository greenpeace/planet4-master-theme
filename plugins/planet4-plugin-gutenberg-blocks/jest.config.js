// The Wordpress Jest config is not extendable,
// so I had to copy its values to this one,
//
// See:
// https://github.com/WordPress/gutenberg/blob/229f0b01e8d1d6e902bc1de8dfae7df916c4583f/packages/scripts/scripts/test-e2e.js#L54
const jestE2EConfig = require('./node_modules/@wordpress/scripts/config/jest-e2e.config.js');

// Run a custom setup script after the Jest environment is initialized.
// see: https://jestjs.io/docs/en/configuration#setupfilesafterenv-array
jestE2EConfig.setupFilesAfterEnv = ['./jest.setup.js'],

module.exports = jestE2EConfig;
