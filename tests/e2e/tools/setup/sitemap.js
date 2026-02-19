import {readFileSync, writeFileSync} from 'node:fs';
import {join} from 'node:path';

const extractLocalLinks = baseURL => {
  const urls = new Set();
  const offset = baseURL.length;
  for (const {href} of document.links) {
    if (href.startsWith(baseURL)) {
      const path = href.slice(offset);
      urls.add(path);
    }
  }
  return Array.from(urls);
};

const ENTRY_POINT = '/topics';
const SITEMAP = join(__dirname, './sitemap.json');

/**
 * Determines URLs and writes them to disk.
 *
 * @param {string} baseURL - The site's base URL.
 * @param {Object} page    - The current page object.
 */
const createSiteMap = async (baseURL, page) => {
  await page.goto(baseURL + ENTRY_POINT);
  const urls = await page.evaluate(extractLocalLinks, baseURL);
  const data = JSON.stringify(urls, null, 4);
  writeFileSync(SITEMAP, data, {encoding: 'utf-8'});
};

/**
 * Reads any previously created site map from disk.
 *
 * @return {Object} Sitemap data.
 */
const readSiteMap = async () => {
  let data = null;
  try {
    data = readFileSync(SITEMAP, {encoding: 'utf-8'});
  } catch (err) {
    return null;
  }
  return JSON.parse(data);
};

export {createSiteMap, readSiteMap};
