import {v4 as uuid} from 'uuid';

// Constants
const {__, sprintf} = wp.i18n;
const ARROW_DIRECTIONS = ['prev', 'next'];
const LAYOUTS = {
  carousel: 'carousel',
  grid: 'grid',
  list: 'list',
};
const BUTTONS_CLASS = '.wp-block-buttons';
const CONTROLS_CLASS = `.${LAYOUTS.carousel}-control`;
const BLOCK_CLASSNAMES = {
  actionsList: 'actions-list',
  postsList: 'posts-list',
};

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

    // This is for the carousel layout, we need to setup the arrows and indicators.
    // Or hide them if there are not enough items to scroll.
    if (layout.className.includes(LAYOUTS.carousel)) {
      const list = layout.querySelector('.wp-block-post-template');
      const isPostsList = layout.className.includes(BLOCK_CLASSNAMES.postsList);
      const isActionsList = layout.className.includes(BLOCK_CLASSNAMES.actionsList);
      if (!list || (!isActionsList && !isPostsList)) {
        return;
      }

      let indicators = null;
      let announcement = null;
      const uniqueId = `${LAYOUTS.carousel}-${uuid()}`;
      const itemsPerSlide = isPostsList ? 4 : 3;

      // Adapt it as bootstrap carousel
      const carousel = document.createElement('div');
      carousel.setAttribute('id', uniqueId);
      carousel.classList.add(LAYOUTS.carousel, 'slide');

      list.classList.add(`${LAYOUTS.carousel}-inner`);
      list.after(carousel);
      carousel.append(list);
      const posts = list.querySelectorAll('.wp-block-post');

      if (isActionsList) {
        // Add some accessibility attributes
        layout.setAttribute('role', 'region');
        layout.setAttribute('aria-label', __('Actions List', 'planet4-master-theme'));
        layout.setAttribute('aria-roledescription', 'carousel');
      }

      // Only add indicators, aria-live element, and back to list link if there are more items to show
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

        if (isActionsList) {
          // Add an aria-live div to announce slide changes.
          announcement = document.createElement('div');
          announcement.setAttribute('aria-live', 'polite');
          announcement.classList.add('visually-hidden');
          announcement.id = `announce-${uniqueId}`;
          layout.appendChild(announcement);

          // Add a hidden link to get back to the list.
          const backToList = document.createElement('a');
          backToList.href = `#${uniqueId}`;
          backToList.textContent = __('Back to Actions List', 'planet4-master-theme');
          backToList.classList.add('carousel-skip-link');
          backToList.setAttribute('role', 'link');
          layout.append(backToList);
        }
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
          // Add a reset link that is only accessible via JS.
          // Only for the Posts List block, not Actions List.
          if (isPostsList) {
            slideReset = document.createElement('a');
            slideReset.classList.add('carousel-ghost-link');
            slideReset.setAttribute('aria-hidden', 'true');
            slideReset.setAttribute('tabindex', '-1');
            slideReset.style.position = 'absolute';
            slideReset.style.opacity = '0';
            slideReset.style.pointerEvents = 'none';
            list.appendChild(slideReset);
          }

          carouselItem = document.createElement('li');
          carouselItem.classList.add('carousel-item', 'carousel-li', `carousel-slide-${totalCarouselItems}`);
          list.append(carouselItem);

          itemWrapper = document.createElement('ul');
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

        if (isPostsList) {
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
                  resetFocusLink.setAttribute('aria-label', sprintf(
                  /* translators: 1: current slide number */
                    __('Slide %1$d', 'planet4-master-theme'),
                    slideIndex + 1
                  ));
                  resetFocusLink.focus();
                }
              }, 600);
            });
          });
        } else if (isActionsList) {
          // Moves the focus to the next slide when the carousel arrows or indicators are hit.
          // Also update the aria-live text so that the screen reader announces the slide change.
          const indicatorButtons = layout.querySelectorAll('.carousel-indicators li');
          [...carouselButtons, ...indicatorButtons].forEach(button => {
            button.addEventListener('click', () => {

              const observer = new MutationObserver(() => {
                const currentSlide = layout.querySelector('.carousel-item.active');
                const match = currentSlide.className.match(/carousel-slide-(\d+)/);
                const slideIndex = match ? parseInt(match[1], 10) : null;
                const slides = layout.querySelectorAll('.carousel-item').length;

                if (announcement) {
                  announcement.innerText = sprintf(
                    /* translators: 1: current slide number, 2: total amount of slides */
                    __('Slide %1$d from %2$d', 'planet4-master-theme'),
                    slideIndex + 1,
                    slides
                  );
                }

                if (!currentSlide) {return;}

                const focusTarget = currentSlide.querySelector('a');

                if (focusTarget) {
                  focusTarget.focus();
                } else {
                  currentSlide.setAttribute('tabindex', '-1');
                  currentSlide.focus();
                }

                observer.disconnect();
              });

              observer.observe(layout, {
                subtree: true,
                attributes: true,
                attributeFilter: ['class'],
              });
            });
          });
        }
      });
    } else {
      // This is for the grid or list layouts, we need to remove the arrows.
      removeArrows(layout);
    }
  }
};
