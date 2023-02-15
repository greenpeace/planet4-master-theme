export const setupQueryCovers = () => {
  const setupCarousel = (cover) => {
    const list = cover.querySelector('.wp-block-post-template');
      
    cover.querySelector('.carousel-query-control-prev').addEventListener('click', (evt) => {
      evt.preventDefault();
      const rect = list.getBoundingClientRect();
      list.style.transform = `translateX(${rect.x + rect.width}px)`;
    });

    cover.querySelector('.carousel-query-control-next').addEventListener('click', (evt) => {
      evt.preventDefault();
      const rect = list.getBoundingClientRect();
      list.style.transform = `translateX(${rect.x - rect.width}px)`;
    });

    return cover;
  }

  const setupQueryAction = (action) => {
    const titleLink = action.querySelector('.wp-block-post-title a');
    const buttonLink = action.querySelector('.wp-block-button__link');

    if(titleLink && buttonLink) {
      buttonLink.href = titleLink.attributes.href.value;
      buttonLink.target = titleLink.attributes.target.value;
      buttonLink.rel = titleLink.attributes.rel.value;
    } 
  }

  const setupLoadMoreFeature = (cover, readMoreBtn) => {
    readMoreBtn.addEventListener('click', (evt) => {
      evt.preventDefault();

      const button = evt.currentTarget;
      const queryId = cover.dataset.queryid;
      const paged = parseInt(cover.dataset.paged) + 1;
      const nextPage = paged + 1;
      const total = parseInt(button.dataset.total);
      const url = `${window.location.href}?query-${queryId}-page=${paged}`;
      
      fetch(url)
        .then(response => {
          return response.text();
        })
        .then(response => {
          const element = document.createElement('div');
          element.innerHTML = response;

          const postTemplate = cover.querySelector('.wp-block-post-template');
          const coverElement = element.querySelector(`.query-loop-cover[data-queryid="${queryId}"]`);
          
          if(coverElement) {
            for (const post of coverElement.querySelectorAll('.wp-block-post-template li')) {
              postTemplate.append(post);
            }
          }

          cover.dataset.paged = paged;
          cover.dataset.nextpage = nextPage;

          if(nextPage > total) {
            button.style.display = 'none';
          }
        })
        .catch(err => {
          console.log(err);
        });
    })
  }
  
  const covers = [
    ...document.querySelectorAll('.query-loop-content-cover'),
    ...document.querySelectorAll('.query-loop-action-cover'),
  ];
  
  for (let cover of covers) {
    for (const action of cover.querySelectorAll('.p4_action')) {
      setupQueryAction(action);
    }

    if(cover.classList.contains('is-style-carousel')) {
      cover = setupCarousel(cover);
    }
    
    const readMoreBtn = cover.querySelector('.btn-view-more-query-loop');
    if(readMoreBtn) {
      setupLoadMoreFeature(cover, readMoreBtn);
    }
  }
}
