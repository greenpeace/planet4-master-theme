const {devices} = require('@playwright/test');
const dotenv = require('dotenv');

const testDir = './tests/e2e';
const envFile = process.env.CI ? `${testDir}/env/.env.ci` : `${testDir}/env/.env.default`;
dotenv.config({path: envFile});
dotenv.config({override: true}); // prioritize .env file if exists

/**
 * @see https://playwright.dev/docs/test-configuration
 */
const config = {
  testDir,
  testMatch: ['*.spec.js', `tickets/${process.env.TICKET}/*.test.js`],
  /* Maximum time one test can run for. */
  timeout: parseInt(process.env.PW_TIMEOUT),
  expect: {
    /**
     * Maximum time expect() should wait for the condition to be met.
     * For example in `await expect(locator).toHaveText();`
     */
    timeout: parseInt(process.env.PW_EXPECT_TIMEOUT),
  },
  /* Run tests in files in parallel */
  fullyParallel: true,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  /* Retry on CI only */
  retries: parseInt(process.env.PW_RETRIES),
  /* Opt out of parallel tests on CI. */
  workers: parseInt(process.env.PW_WORKERS),
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: process.env.CI ?
    [['html', {outputFolder: 'e2e-report'}],
      ['junit', {outputFile: 'results.xml'}]] :
    [['html', {outputFolder: 'e2e-report'}]],
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Maximum time each action such as `click()` can take. Defaults to 0 (no limit). */
    actionTimeout: parseInt(process.env.PW_ACTION_TIMEOUT),
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: process.env.WP_BASE_URL,

    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  /* Configure projects for major browsers */
  projects: [
    {name: 'setup', testMatch: /setup\/.*\.setup\.js/},
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
      },
      dependencies: ['setup'],
    },

    {
      name: 'firefox',
      use: {
        ...devices['Desktop Firefox'],
        contextOptions: {
          ignoreHTTPSErrors: true,
        },
      },
      dependencies: ['setup'],
    },

    {
      name: 'webkit',
      use: {
        ...devices['Desktop Safari'],
      },
      dependencies: ['setup'],
    },

    /* Test against mobile viewports. */
    // {
    //   name: 'Mobile Chrome',
    //   use: {
    //     ...devices['Pixel 5'],
    //   },
    // },
    // {
    //   name: 'Mobile Safari',
    //   use: {
    //     ...devices['iPhone 12'],
    //   },
    // },

    /* Test against branded browsers. */
    // {
    //   name: 'Microsoft Edge',
    //   use: {
    //     channel: 'msedge',
    //   },
    // },
    // {
    //   name: 'Google Chrome',
    //   use: {
    //     channel: 'chrome',
    //   },
    // },
  ],

  /* Folder for test artifacts such as screenshots, videos, traces, etc. */
  outputDir: 'e2e-results/',

  /* Run your local dev server before starting the tests */
  // webServer: {
  //   command: 'npm run start',
  //   port: 3000,
  // },
  globalSetup: require.resolve('./tests/e2e/tools/setup/global-setup'),
};

module.exports = config;
