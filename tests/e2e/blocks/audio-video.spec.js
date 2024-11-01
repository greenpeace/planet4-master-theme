import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor.js';

const YOUTUBE_TEST = 'https://www.youtube.com/watch?v=-CwkccAgKrs';
const VIMEO_TEST = 'https://vimeo.com/100195272';
const MP4_TEST = 'https://www.greenpeace.org/static/planet4-assets/tests/edge_of_the_wrold.mp4';

const SOUNDCLOUD_TEST = 'https://soundcloud.com/greenpeaceuk-1/04-and-we-will-defend-requiem';
const MP3_TEST = 'https://www.greenpeace.org/static/planet4-assets/tests/wochenserie_greenpeace.mp3';

/**
 * Add a Video or Audio block to a page with a specific link.
 *
 * @param {Object} page - The page object for interacting with the browser.
 * @param {string} mediaLink - The video file link (can be YouTube, Vimeo, mp4).
 * @param {string} mediaType - The type of media added (Audio or Video).
 */
const addVideoOrAudioBlock = async ({page}, mediaLink, mediaType) => {
  await searchAndInsertBlock({page}, mediaType);
  await page.getByRole('button', {name: 'Insert from URL'}).click();
  await page.getByPlaceholder('Paste or type URL').fill(mediaLink);
  await page.keyboard.press('Enter');
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

  // Create a post for the test.
  await createPostWithFeaturedImage({admin, editor}, {title: 'Test Audio and Video blocks'});

  // Add Video blocks with the various examples.
  await addVideoOrAudioBlock({page}, YOUTUBE_TEST, 'Video');
  await addVideoOrAudioBlock({page}, VIMEO_TEST, 'Video');
  await addVideoOrAudioBlock({page}, MP4_TEST, 'Video');

  // Add Audio blocks with the various examples.
  await addVideoOrAudioBlock({page}, MP3_TEST, 'Audio');
  await addVideoOrAudioBlock({page}, SOUNDCLOUD_TEST, 'Audio');

  // Publish page.
  await publishPostAndVisit({page, editor});

  // Check on the frontend that the blocks are present.
  // Check YouTube embed.
  const youtubeEmbed = page.locator('.wp-block-embed-youtube');
  const youtubeVideoId = YOUTUBE_TEST.split('?v=')[1];
  await expect(youtubeEmbed).toBeVisible();
  await expect(youtubeEmbed.locator('lite-youtube')).toHaveAttribute('videoid', youtubeVideoId);

  // Check Vimeo embed.
  await expect(page.locator('.wp-block-embed-vimeo')).toBeVisible();

  // Check MP4 embed.
  await expect(page.locator('.wp-block-video > video')).toHaveAttribute('src', MP4_TEST);

  // Check SoundCloud embed.
  await expect(page.locator('.wp-block-embed-soundcloud')).toBeVisible();

  // Check MP3 embed.
  await expect(page.locator('.wp-block-audio > audio')).toHaveAttribute('src', MP3_TEST);
});
