import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor.js';

test.useAdminLoggedIn();

const slides = [
  {
    image: 357,
    focal_points: {},
    header: 'My test Header 1',
    description: 'Testing carousel description 1',
    link_text: 'Read more 1',
    link_url: 'https://google.com',
    link_url_new_tab: false,
  },
  {
    image: 354,
    focal_points: {},
    header: 'My test Header 2',
    description: 'Testing carousel description 2',
    link_text: 'Read more 2',
    link_url: 'https://yahoo.com',
    link_url_new_tab: true,
  },
];

/**
 * Resolve an attachment's image URL, alt text, and srcset via wp.data,
 * mirroring what the block would populate for a selected image.
 *
 * @param {{Page}} page    Playwright page object
 * @param {number} imageId Attachment post ID
 */
const getImageDetails = async (page, imageId) => {
  return page.evaluate(async id => {
    const imageRecord = await wp.data.resolveSelect('core').getEntityRecord(
      'postType',
      'attachment',
      id
    );

    const sizes = imageRecord.media_details?.sizes || {};
    const image_srcset = Object.values(sizes)
      .map(size => `${size.source_url} ${size.width}w`)
      .join(',');

    return {
      image_url: imageRecord.source_url,
      image_alt: imageRecord.alt_text || '',
      image_srcset,
    };
  }, imageId);
};

/**
 * Set the Carousel Header block's attributes directly via wp.data,
 * bypassing the block editor UI entirely.
 *
 * @param {{Page}} page       Playwright page object
 * @param {Object} attributes Block attributes to apply (carousel_autoplay, slides, currentImageIndex)
 */
const setCarouselAttributes = async (page, attributes) => {
  await page.evaluate(attrs => {
    const blocks = wp.data.select('core/block-editor').getBlocks();
    const carouselBlock = blocks.find(block => block.name === 'planet4-blocks/carousel-header');

    if (!carouselBlock) {
      throw new Error('Carousel header block not found on the page');
    }

    wp.data.dispatch('core/block-editor').updateBlockAttributes(carouselBlock.clientId, attrs);
  }, attributes);
};

test('Create and check carousel header block', async ({page, admin, editor}) => {
  test.slow();
  await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Carousel', postType: 'page'});

  // Add block
  await searchAndInsertBlock({editor, page}, 'planet4-blocks/carousel-header');

  // Resolve each slide's image details (url, alt, srcset) from the attachment record
  const slideAttributes = await Promise.all(
    slides.map(async slide => {
      const imageDetails = await getImageDetails(page, slide.image);

      return {
        ...slide,
        ...imageDetails,
      };
    })
  );

  await setCarouselAttributes(page, {
    carousel_autoplay: false,
    slides: slideAttributes,
    currentImageIndex: 0,
  });

  // Publish Page
  await publishPostAndVisit({page, editor});

  // Assertions
  const h2Title1 = await page.innerText('.carousel-captions-wrapper h2');
  const paragraphDescription1 = await page.innerHTML('.carousel-captions-wrapper p');
  const ctaButton1 = page.locator('.action-button a').first();

  expect(h2Title1).toBe(slides[0].header);
  expect(paragraphDescription1).toBe(slides[0].description);
  await expect(ctaButton1).toBeVisible();

  await page.locator('button.carousel-control-next').click();

  const h2Title2 = await page.innerText('.carousel-captions-wrapper h2>>nth=1');
  const paragraphDescription2 = await page.innerHTML('.carousel-captions-wrapper p>>nth=1');
  const ctaButton2 = page.locator('.action-button a').nth(1);

  expect(h2Title2).toBe(slides[1].header);
  expect(paragraphDescription2).toBe(slides[1].description);
  await expect(ctaButton2).toBeVisible();
});
