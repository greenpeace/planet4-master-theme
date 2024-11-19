import {test, expect} from '../tools/lib/test-utils.js';
import {publishPost, updatePost, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor.js';

const YOUTUBE_TEST = 'https://www.youtube.com/watch?v=-CwkccAgKrs';
const VIMEO_TEST = 'https://vimeo.com/120680405';
const MP4_TEST = 'https://www.greenpeace.org/static/planet4-assets/tests/edge_of_the_wrold.mp4';

const SOUNDCLOUD_TEST = 'https://soundcloud.com/greenpeaceuk-1/04-and-we-will-defend-requiem';
const MP3_TEST = 'https://www.greenpeace.org/static/planet4-assets/tests/wochenserie_greenpeace.mp3';

let postLink;

/**
 * Add a Video or Audio block to a page with a specific link.
 *
 * @param {Object} page - The page object for interacting with the browser.
 * @param {string} mediaType - The type of media added (audio or video).
 * @param {string} mediaLink - The media file link (can be YouTube, Vimeo, mp4, mp3, SoundCloud).
 */
const addAudioOrVideoBlock = async ({page}, mediaType, mediaLink) => {
  await searchAndInsertBlock({page}, mediaType, mediaType);
  await page.getByRole('button', {name: 'Insert from URL'}).click();
  await page.getByPlaceholder('Paste or type URL').fill(mediaLink);
  await page.keyboard.press('Enter');
  await updatePost({page});
};

/**
 * Check an Audio or Video block in the frontend.
 *
 * @param {Object} page - The page object for interacting with the browser.
 * @param {string} blockLocator - The block classname.
 * @param {boolean} backToEditor - Whether or not we need to go back to the editor after checking the block.
 */
const checkBlockInFrontend = async ({page}, blockLocator, backToEditor = true) => {
  await page.goto(postLink);
  await expect(page.locator(blockLocator)).toBeVisible();
  if (backToEditor) {
    await page.goBack();
  }
};

test.useAdminLoggedIn();

test('check the Audio and Video blocks', async ({page, admin, editor}) => {
  // Create and publish the post.
  await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Audio and Video blocks'});
  postLink = await publishPost({page, editor});

  // Test video blocks.
  await addAudioOrVideoBlock({page}, 'video', YOUTUBE_TEST);
  await checkBlockInFrontend({page}, '.wp-block-embed-youtube');

  await addAudioOrVideoBlock({page}, 'video', VIMEO_TEST);
  await checkBlockInFrontend({page}, '.wp-block-embed-vimeo');

  await addAudioOrVideoBlock({page}, 'video', MP4_TEST);
  await checkBlockInFrontend({page}, '.wp-block-video');

  // Test audio blocks.
  await addAudioOrVideoBlock({page}, 'audio', SOUNDCLOUD_TEST);
  await checkBlockInFrontend({page}, '.wp-block-embed-soundcloud');

  await addAudioOrVideoBlock({page}, 'audio', MP3_TEST);
  await checkBlockInFrontend({page}, '.wp-block-audio', false);
});
