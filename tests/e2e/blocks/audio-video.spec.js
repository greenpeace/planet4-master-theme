import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock, closeBlockInserter} from '../tools/lib/editor.js';

const TEST_MEDIA = [
  {
    type: 'video',
    format: 'youtube',
    url: 'https://www.youtube.com/watch?v=-CwkccAgKrs',
    selector: '.wp-block-embed-youtube',
  },
  {
    type: 'video',
    format: 'vimeo',
    url: 'https://vimeo.com/120680405',
    selector: '.wp-block-embed-vimeo',
  },
  {
    type: 'video',
    format: 'mp4',
    url: 'https://www.greenpeace.org/static/planet4-assets/tests/edge_of_the_wrold.mp4',
    selector: '.wp-block-video > video',
  },
  {
    type: 'audio',
    format: 'soundcloud',
    url: 'https://soundcloud.com/greenpeaceuk-1/04-and-we-will-defend-requiem',
    selector: '.wp-block-embed-soundcloud',
  },
  {
    type: 'audio',
    format: 'mp3',
    url: 'https://www.greenpeace.org/static/planet4-assets/tests/wochenserie_greenpeace.mp3',
    selector: '.wp-block-audio > audio',
  },
];

/**
 * Add a Video or Audio block to a page with a specific link.
 *
 * @param {{Page}} page      - The page object for interacting with the browser.
 * @param {string} mediaType - The type of media added (Audio or Video).
 * @param {string} mediaLink - The media file link (can be YouTube, Vimeo, mp4, mp3, SoundCloud).
 */
const addVideoOrAudioBlock = async ({page}, mediaType, mediaLink) => {
  await searchAndInsertBlock({page}, mediaType, mediaType);

  // Make sure the block has been added.
  const insertUrl = await page.getByRole('button', {name: 'Insert from URL'});
  await expect(insertUrl).toBeVisible();

  // We should close the sidebar before editing the block.
  await closeBlockInserter({page});

  // Add the media URL.
  await insertUrl.click();
  await page.getByPlaceholder('Paste or type URL').fill(mediaLink);
  await page.keyboard.press('Enter');
};

test.useAdminLoggedIn();

// We skip these tests for now, because they often don't pass and block PRs.
TEST_MEDIA.forEach(({type, format, url, selector}) => {
  test.skip(`check the ${type} block with format ${format}`, async ({page, admin, editor}) => {
    await createPostWithFeaturedImage({page, admin, editor}, {title: `Test ${type} block`});
    await addVideoOrAudioBlock({page}, type, url);
    await publishPostAndVisit({page, editor});
    await expect(page.locator(selector)).toBeVisible();
  });
});
