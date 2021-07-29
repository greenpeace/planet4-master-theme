// As with the Jest config, the Puppeteer is also
// not fully extendable.
//
// See:
// https://github.com/WordPress/gutenberg/blob/229f0b01e8d1d6e902bc1de8dfae7df916c4583f/packages/scripts/scripts/test-e2e.js#L54
const puppeteerConfig = require('./node_modules/@wordpress/scripts/config/puppeteer.config.js');

// Ignore invalid certificate errors
puppeteerConfig.launch.ignoreHTTPSErrors = true;
puppeteerConfig.launch.args = ['--no-sandbox', '--disable-setuid-sandbox'];

module.exports = puppeteerConfig;
