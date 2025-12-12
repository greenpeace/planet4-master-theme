import {v4 as uuid} from 'uuid';

// Constants
const {__} = wp.i18n;
const ARROW_DIRECTIONS = ['prev', 'next'];
const LAYOUTS = {
  carousel: 'carousel',
  grid: 'grid',
  list: 'list',
};
const BUTTONS_CLASS = '.wp-block-buttons';
const CONTROLS_CLASS = `.${LAYOUTS.carousel}-control`;

/**
 * This function removes the arrows, in one of these two cases:
 * the layout isn't carousel, or it doesn't have enough items to scroll.
 *
 * @param {string} layout - The layout of the Query Loop block: can be grid, list, or carousel.
 *
 * @return {void}
 */
const removeArrows = layout => ARROW_DIRECTIONS.forEach(direction => {
  const controlBtn = layout.querySelector(`${BUTTONS_CLASS} ${CONTROLS_CLASS}-${direction}`);
  if (controlBtn && controlBtn.parentNode) {
    controlBtn.parentNode.removeChild(controlBtn);
  }
});

/**
 * This function is used to setup the various custom layouts that we
 * created for the Query Loop block for our Actions List and Posts List blocks.
 *
 * The available layouts are grid, list, and carousel.
 *
 * @return {void}
 */
export const setupQueryLoopCarousel = () => {
  for (const layout of document.querySelectorAll('[class*="is-custom-layout-"]')) {
    const hasValidLayout = layout && Object.keys(LAYOUTS).find(l => layout.className.includes(l));
    if (!hasValidLayout) {
      return;
    }

    if (layout.className.includes(LAYOUTS.carousel)) {
      // This is for the carousel layout, we need to setup the arrows and indicators.
      // Or hide them if there are not enough items to scroll.
      const list = layout.querySelector('.wp-block-post-template');
      if (!list) {
        return;
      }
      let indicators = null;
      const uniqueId = `${LAYOUTS.carousel}-${uuid()}`;
      const isPostsList = layout.className.includes('posts-list');
      const itemsPerSlide = isPostsList ? 4 : 3;

      // Adapt it as bootstrap carousel
      const carousel = document.createElement('div');
      carousel.setAttribute('id', uniqueId);
      carousel.classList.add(LAYOUTS.carousel, 'slide');

      list.classList.add(`${LAYOUTS.carousel}-inner`);
      list.after(carousel);
      carousel.append(list);
      const posts = list.querySelectorAll('.wp-block-post');

      const backToList = document.createElement('a');
      backToList.href = `#${uniqueId}`;
      backToList.textContent = __('Back to List', 'planet4-master-theme-backend');
      backToList.classList.add('carousel-skip-link');
      backToList.setAttribute('role', 'link');

      layout.append(backToList);

      // Only add indicators if there are more items to show
      if (posts.length > itemsPerSlide) {
        indicators = document.createElement('ol');
        indicators.classList.add(`${LAYOUTS.carousel}-indicators`);
        carousel.append(indicators);

        // Update controls
        ARROW_DIRECTIONS.forEach(direction => {
          const controlBtn = layout.querySelector(`${BUTTONS_CLASS} ${CONTROLS_CLASS}-${direction}`);
          if (!controlBtn) {
            return;
          }
          controlBtn.dataset.bsTarget = `#${uniqueId}`;
          controlBtn.dataset.bsSlide = direction;

          const link = controlBtn.querySelector('button');
          if (link) {
            link.classList.add('visually-hidden');
          }
        });

        // Align the controls in the middle
        const controls = layout.querySelector(BUTTONS_CLASS);
        controls.style.top = (list.getBoundingClientRect().height / 2) - (controls.getBoundingClientRect().height / 2);
      } else {
        // Remove arrows if they are not needed
        removeArrows(layout);
      }

      let carouselItem,
        itemWrapper,
        indicator,
        slideReset,
        totalCarouselItems = 0;

      posts.forEach((post, index) => {
        if (index % itemsPerSlide === 0) {
          // Reset link that is only accessible via JS
          slideReset = document.createElement('a');
          slideReset.classList.add('carousel-ghost-link');
          slideReset.setAttribute('aria-hidden', 'true');
          slideReset.setAttribute('tabindex', '-1');
          slideReset.style.position = 'absolute';
          slideReset.style.opacity = '0';
          slideReset.style.pointerEvents = 'none';
          list.appendChild(slideReset);

          carouselItem = document.createElement('li');
          carouselItem.classList.add('carousel-item', 'carousel-li', `carousel-slide-${totalCarouselItems}`);
          list.append(carouselItem);

          itemWrapper = document.createElement('div');
          itemWrapper.classList.add('carousel-item-wrapper');

          carouselItem.append(itemWrapper);

          if (indicators) {
            indicator = document.createElement('li');
            indicator.classList.add('carousel-li');
            indicator.dataset.bsTarget = `#${uniqueId}`;
            indicator.dataset.bsSlideTo = totalCarouselItems;
            if (index === 0) {
              indicator.classList.toggle('active');
            }
            indicators.append(indicator);
          }

          if (index === 0) {
            carouselItem.classList.toggle('active');
          }

          totalCarouselItems++;
        }

        itemWrapper.append(post);

        const carouselButtons = layout.querySelectorAll(`${BUTTONS_CLASS} ${CONTROLS_CLASS}-next, ${BUTTONS_CLASS} ${CONTROLS_CLASS}-prev`);

        // This resets the focus to the ghost element so users can tab over the new slide.
        carouselButtons.forEach(button => {
          button.addEventListener('click', () => {
            setTimeout(() => {
              const resetFocusLink = layout.querySelector('.carousel-ghost-link');
              const currentSlide = layout.querySelector('.carousel-item.active');
              if (resetFocusLink) {
                const match = currentSlide.className.match(/carousel-slide-(\d+)/);
                const slideIndex = match ? parseInt(match[1], 10) : null;

                // This adds a voice over so the screen reader reads out which Slide you are on.
                resetFocusLink.removeAttribute('aria-hidden');
                resetFocusLink.setAttribute('aria-label', `Slide ${slideIndex}`);
                resetFocusLink.focus();
              }
            }, 600);
          });
        });
      });
    } else {
      // This is for the grid or list layouts, we need to remove the arrows.
      removeArrows(layout);
    }
  }
};
