const playwright = require('@playwright/test');

import {createSiteMap, readSiteMap} from './sitemap';

export default async () => {
  // only create site map if it doesn't already exist
  try {
    readSiteMap();
  } catch (err) {
    // launch browser and initiate crawler
    let browser = playwright.devices['Desktop Chrome'].defaultBrowserType;
    browser = await playwright[browser].launch();
    const page = await browser.newPage();
    await createSiteMap(process.env.WP_BASE_URL, page);
    await browser.close();
  }
};
