import {v4 as uuid} from 'uuid';

const ARROW_DIRECTIONS = ['prev', 'next'];

const removeArrows = layout => ARROW_DIRECTIONS.forEach(direction => {
  const controlBtn = layout.querySelector(`.wp-block-buttons .carousel-control-${direction}`);
  controlBtn?.parentNode.removeChild(controlBtn);
});

export const setupQueryLoopCarousel = () => {
  for (const layout of document.querySelectorAll('[class*="is-custom-layout-"]')) {
    const list = layout.querySelector('.wp-block-post-template');
    let indicators = null;

    // Only apply to carousel view
    if (layout.className.includes('carousel')) {
      const uniqueId = `carousel-${uuid()}`;
      const isPostsList = layout.className.includes('posts-list');
      const itemsPerSlide = isPostsList ? 4 : 3;

      // Adapt it as bootstrap carousel
      const carousel = document.createElement('div');
      carousel.setAttribute('id', uniqueId);
      carousel.classList.add('carousel', 'slide');
      carousel.dataset.bsRide = 'carousel';
      carousel.dataset.bsInterval = 'false';

      list.classList.add('carousel-inner');
      list.after(carousel);
      carousel.append(list);

      if (!list) {
        return;
      }
      const posts = list.querySelectorAll('.wp-block-post');

      // Only add indicators if there are more items to show
      if (posts.length > itemsPerSlide) {
        indicators = document.createElement('ol');
        indicators.classList.add('carousel-indicators');
        carousel.append(indicators);

        // Update controls
        ARROW_DIRECTIONS.forEach(direction => {
          const controlBtn = layout.querySelector(`.wp-block-buttons .carousel-control-${direction}`);
          controlBtn.dataset.bsTarget = `#${uniqueId}`;
          controlBtn.dataset.bsSlide = direction;

          const link = controlBtn.querySelector('a');
          if (link) {
            link.classList.add('visually-hidden');
          }
        });

        // Align the controls in the middle
        const controls = layout.querySelector('.wp-block-buttons');
        controls.style.top = (list.getBoundingClientRect().height / 2) - (controls.getBoundingClientRect().height / 2);
      } else {
        // Remove arrows if they are not needed
        removeArrows(layout);
      }

      let carouselItem,
        itemWrapper,
        indicator,
        totalCarouselItems = 0;

      posts.forEach((post, index) => {
        if (index % itemsPerSlide === 0) {
          carouselItem = document.createElement('li');
          carouselItem.classList.add('carousel-item', 'carousel-li');
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
      });
    } else if (layout.className.includes('grid') || layout.className.includes('list')) {
      // Remove arrows for grid and list layouts.
      removeArrows(layout);
    }
  }
};
