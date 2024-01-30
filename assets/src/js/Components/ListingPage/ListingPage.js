import {useState, useEffect} from '@wordpress/element';
import {fetchJson} from '../../../functions/fetchJson';
import {addQueryArgs} from '../../../functions/addQueryArgs';

const {apiFetch} = wp;

//   const listViewToggle = document.querySelector('.list-view-toggle');
//   const gridViewToggle = document.querySelector('.grid-view-toggle');

//   const listingPageContent = document.getElementById('listing-page-content');

//   if (!listingPageContent || !listViewToggle || !gridViewToggle) {
//     return;
//   }

//   const switchViews = () => {
//     listingPageContent.classList.toggle('wp-block-query--list');
//     listingPageContent.classList.toggle('wp-block-query--grid');
//     gridViewToggle.classList.toggle('d-none');
//     listViewToggle.classList.toggle('d-none');
//   };

//   listViewToggle.onclick = switchViews;
//   gridViewToggle.onclick = switchViews;

export const ListingPage = () => {
  const [posts, setPosts] = useState({recent_posts: [], total_posts: 0})

  useEffect(() => {
    (async () => {
      try {
        const args = {
          article_count: 3,
          ignore_categories: false,
          offset: 0,
        };

        const baseUrl = document.body.dataset.nro;

        const data = baseUrl ?
          await fetchJson(`${baseUrl}/wp-json/${addQueryArgs('planet4/v1/get-posts', args)}`) :
          await apiFetch({path: addQueryArgs('planet4/v1/get-posts', args)});

        setPosts(data);

      } catch (e) {
        // eslint-disable-next-line no-console
        console.log(e);
      }
      // http://www.planet4.test/wp-json/planet4/v1/get-posts?article_count=3&ignore_categories=false&offset=0
    })();

  }, []);

  return (
    <section>
      <div>
        <select>
          <option>Post type</option>
          <option>Action type</option>
        </select>
      </div>
      Total {posts.total_posts} posts.
    </section>
  )
};

export default ListingPage;
