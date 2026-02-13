import {createRoot, useEffect, useState} from '@wordpress/element';

export default function SearchController({restUrl}) {
  const [searchTerm, setSearchTerm] = useState('');
  const [results, setResults] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [foundPosts, setFoundPosts] = useState(0);
  const [postsPerLoad, setPostsPerLoad] = useState(5);
  const [loading, setLoading] = useState(false);

  // Fetch search results from the API:
  const fetchResults = async (page = 1, append = false, filters = {}) => {
    const wrapper = document.getElementById('search-results-wrapper');
    const resultsContainer = document.querySelector('#search-results .list-unstyled');
    const input = document.getElementById('search-page-input');

    if (!wrapper || !resultsContainer || !input) {return;}

    setLoading(true);

    const params = new URLSearchParams();
    params.set('s', input.value);
    params.set('paged', page);

    if (Object.keys(filters).length) {
      params.set(filters.name, filters.value);
    }

    const url = `${restUrl}?${params.toString()}`;
    const res = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
    const data = await res.json();

    const html = data.html.replace(/\n/g, '');
    resultsContainer.innerHTML = append ? resultsContainer.innerHTML + html : html;

    // Update state
    setSearchTerm(input.value);
    setResults(resultsContainer.innerHTML);
    setCurrentPage(data.current_page);
    setFoundPosts(data.found_posts);
    setPostsPerLoad(data.posts_per_load || 5);

    history.pushState({}, '', `?${params.toString()}`);
    setLoading(false);
  };

  // Show more results when the Load More button is clicked:
  const onLoadMore = () => {
    if (loading) {return;}
    const nextPage = currentPage + 1;
    fetchResults(nextPage, true);
  };

  // Populate the search results list when the Search button is clicked:
  const onSubmit = e => {
    e.preventDefault();
    fetchResults(1, false);
  };

  // Populate the search results list when the filters are selected:
  const onFilter = (e, li) => {
    if (e.target.tagName === 'INPUT') {return;} // Prevent double toggling if the user clicked the <input> directly

    const checkbox = li.querySelector('input[type="checkbox"]');
    if (!checkbox) {return;}

    checkbox.checked = !checkbox.checked;

    const name = checkbox.name;
    const value = checkbox.value;

    fetchResults(1, false, {name, value});
  };

  // Populate the search results list on component mount:
  useEffect(() => {
    fetchResults(1, false);
  }, []);

  // Update the title:
  useEffect(() => {
    const title = document.querySelector('.result-statement');
    title.innerHTML = `${foundPosts} results for '${searchTerm}'`;
  }, [results]);

  // Update the Load More button text:
  useEffect(() => {
    const loadMoreButton = document.querySelector('.btn-load-more-click-scroll');
    const remainingPosts = foundPosts - (currentPage * postsPerLoad);
    const valueToShow = (remainingPosts < postsPerLoad) ? remainingPosts : postsPerLoad;

    loadMoreButton.innerHTML = `Show ${valueToShow} more results`;
  }, [results]);

  // Add and remove listeners:
  useEffect(() => {
    const form = document.getElementById('search_form_inner');
    const loadMoreButton = document.querySelector('.btn-load-more-click-scroll');
    const filters = document.querySelectorAll('#filter-sidebar-options .filteritem li');

    if (form) {
      form.addEventListener('submit', onSubmit);
    }
    if (loadMoreButton) {
      loadMoreButton.addEventListener('click', onLoadMore);
    };
    if (filters) {
      filters.forEach(filter => {
        filter.addEventListener('click', e => onFilter(e, filter));
      });
    }

    return () => {
      if (form) {
        form.removeEventListener('submit', onSubmit);
      }
      if (loadMoreButton) {
        loadMoreButton.removeEventListener('click', onLoadMore);
      }
    };
  });

  // Hide the Load More button:
  useEffect(() => {
    const loadMoreButton = document.querySelector('.btn-load-more-click-scroll');
    const showMore = currentPage * postsPerLoad < foundPosts;

    loadMoreButton.style.display = showMore ? 'block' : 'none';

  }, [currentPage, foundPosts, postsPerLoad]);

  return null;
}

// Mount the component
const el = document.getElementById('search-controller');
if (el) {
  createRoot(el).render(
    <SearchController restUrl={el.dataset.restUrl} />
  );
}
