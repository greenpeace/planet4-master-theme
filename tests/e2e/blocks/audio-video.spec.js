import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor.js';

const YOUTUBE_TEST = 'https://www.youtube.com/watch?v=-CwkccAgKrs';
const VIMEO_TEST = 'https://vimeo.com/120680405';
const MP4_TEST = 'https://www.greenpeace.org/static/planet4-assets/tests/edge_of_the_wrold.mp4';

const SOUNDCLOUD_TEST = 'https://soundcloud.com/greenpeaceuk-1/04-and-we-will-defend-requiem';
const MP3_TEST = 'https://www.greenpeace.org/static/planet4-assets/tests/wochenserie_greenpeace.mp3';

/**
 * Add a Video or Audio block to a page with a specific link.
 *
 * @param {Object} page - The page object for interacting with the browser.
 * @param {string} mediaType - The type of media added (audio or video).
 * @param {string} mediaProvider - The media provider (youtube, soundcloud, etc).
 * @param {string} mediaLink - The media file link (can be YouTube, Vimeo, mp4, mp3, SoundCloud).
 */
const testAudioOrVideoBlock = async ({page, admin, editor}, mediaType, mediaProvider, mediaLink) => {
  await createPostWithFeaturedImage({page, admin, editor}, {title: `Test Audio and Video blocks - ${mediaProvider}`});

  // Add the video/audio block to the post.
  await searchAndInsertBlock({page}, mediaType, mediaType);
  await page.getByRole('button', {name: 'Insert from URL'}).click();
  await page.getByPlaceholder('Paste or type URL').fill(mediaLink);
  await page.keyboard.press('Enter');

  // Publish post.
  await publishPostAndVisit({page, editor});

  // Check on the frontend that the block is present.
  await expect(page.locator(`.wp-block-${mediaProvider}`)).toBeVisible();
};

test.useAdminLoggedIn();

test('check the Audio and Video blocks', async ({page, admin, editor}) => {
  // Make sure the "Lazy Youtube player" setting is enabled.
  await page.goto('./wp-admin/admin.php?page=planet4_settings_features');
  const lazyYoutubePlayerSetting = page.locator('#lazy_youtube_player');
  const alreadyEnabled = await lazyYoutubePlayerSetting.isChecked();
  if (!alreadyEnabled) {
    await lazyYoutubePlayerSetting.check();
    await page.locator('input[type="submit"]').click();
  }

  // Test video blocks.
  await testAudioOrVideoBlock({page, admin, editor}, 'video', 'youtube', YOUTUBE_TEST);
  await testAudioOrVideoBlock({page, admin, editor}, 'video', 'vimeo', VIMEO_TEST);
  await testAudioOrVideoBlock({page, admin, editor}, 'video', 'video', MP4_TEST);

  // Test audio blocks.
  await testAudioOrVideoBlock({page, admin, editor}, 'audio', 'soundcloud', SOUNDCLOUD_TEST);
  await testAudioOrVideoBlock({page, admin, editor}, 'audio', 'audio', MP3_TEST);
});
