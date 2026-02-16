import {createRoot, useEffect, useState, useRef, useCallback} from '@wordpress/element';

const API_SEARCH = {
  posts: 'planet4/v1/search',
  terms: 'planet4/v1/search-taxonomies',
};

const ROOT_CONFIG = {
  categories: '#item-issue',
  contentTypes: '#item-content',
  postTypes: '#item-post-types',
  actionTypes: '#item-action',
  searchTitle: '#result-statement',
  searchForm: '#search-bar',
  sortFilter: '#sort-filter',
  loadMoreButton: '.load-more-button-div',
};

/* ---------------------------
   Components
---------------------------- */

// Render the filters:
function FilterList({
  loading,
  items = {},
  filterNamespace,
  gaAction,
  getKey,
  getLabel,
  getAriaSubject,
}) {
  if (loading) {
    return <div className="search-meta">Loading…</div>;
  }

  const list = Object.values(items);

  return (
    <ul className="list-unstyled">
      {list.map(item => {
        const count = item.results ?? item.count ?? 0;
        const label = getLabel(item);
        const key = getKey(item);

        const ariaLabel =
          count === 1 ?
            `Filter results by ${getAriaSubject} ${label}, 1 result was found` :
            `Filter results by ${getAriaSubject} ${label}, ${count} results were found`;

        return (
          <li key={key}>
            {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
            <label className="custom-control">
              <input
                type="checkbox"
                name={`f[${filterNamespace}][${label}]`}
                value={item.id}
                className="p4-custom-control-input"
                data-ga-category="Search Page"
                data-ga-action={gaAction}
                data-ga-label={label}
                aria-label={ariaLabel}
              />
              <span className="custom-control-description">
                {label} {count > 0 && `(${count})`}
              </span>
            </label>
          </li>
        );
      })}
    </ul>
  );
}

// Render the categories filter:
function CategoriesFilter({loading, categories}) {
  return (
    <FilterList
      loading={loading}
      items={categories}
      filterNamespace="cat"
      gaAction="Category Filter"
      getKey={item => item.id}
      getLabel={item => item.name}
      getAriaSubject="category"
    />
  );
}

// Render the content types filter:
function ContentTypesFilter({loading, contentTypes}) {
  return (
    <FilterList
      loading={loading}
      items={contentTypes}
      filterNamespace="ctype"
      gaAction="Content Type Filter"
      getKey={item => item.slug}
      getLabel={item => item.slug}
      getAriaSubject="content type"
    />
  );
}

// Render the post types filter:
function PostTypesFilter({loading, postTypes}) {
  return (
    <FilterList
      loading={loading}
      items={postTypes}
      filterNamespace="ptype"
      gaAction="Post Type Filter"
      getKey={item => item.id}
      getLabel={item => item.name}
      getAriaSubject="post type"
    />
  );
}

// Render the action types filter:
function ActionTypesFilter({loading, actionTypes}) {
  return (
    <FilterList
      loading={loading}
      items={actionTypes}
      filterNamespace="atype"
      gaAction="Action Type Filter"
      getKey={item => item.id}
      getLabel={item => item.name}
      getAriaSubject="action type"
    />
  );
}

// Render the load more button:
function LoadMoreButton({foundPosts, currentPage, postsPerLoad, onLoadMore}) {
  const remainingPosts = foundPosts - (currentPage * postsPerLoad);
  const valueToShow = remainingPosts < postsPerLoad ? remainingPosts : postsPerLoad;
  const showMore = currentPage * postsPerLoad < foundPosts;

  if (!showMore) {return null;}

  return (
    <button
      className="btn btn-secondary more-btn btn-load-more-click-scroll"
      onClick={onLoadMore}
    >
      Show {valueToShow} more results
    </button>
  );
}

// Render the sort-by filter:
function SortFilter({foundPosts}) {
  if (foundPosts === 0) {return;}

  return (
    <div className="select-container">
      <label htmlFor="select_order">Sort by</label>
      <select
        id="select_order"
        className="form-select"
        name="select_order"
        data-ga-category="Search Page"
        data-ga-action="Sort By Filter"
        data-ga-label=""
      >
        <option value="_score">Most relevant</option>
        <option value="post_date">Newest</option>
        <option value="post_date_asc">Oldest</option>
      </select>
    </div>
  );
}

// Render the search title section:
function SearchTitle({foundPosts, searchTerm}) {
  return (
    <>
      <h1 className="result-statement">
        {foundPosts} results for {searchTerm}
      </h1>
      {foundPosts === 0 && (
        <p className="search-info">
          We&apos;re sorry we couldn&apos;t find any matches for your search term
        </p>
      )}
      { foundPosts === 0 && (
        <ul className="search-help-info">
          <li>Check for typos, and try your search again</li>
          <li>Try searching for something else</li>
        </ul>
      )}
    </>
  );
}

// Render the search form:
function SearchForm({setSearchTerm, siteUrl, onSubmit, searchTerm}) {
  return (
    <form
      id="search_form_inner"
      method="get"
      role="search"
      className="form d-md-flex"
      action={siteUrl}
      onSubmit={onSubmit}
    >
      <div className="search-input-container w-100">
        <input
          type="search"
          id="search-page-input"
          className="form-control"
          placeholder="Search by name, keyword, or topic"
          value={searchTerm}
          onChange={e => setSearchTerm(e.target.value)}
          name="s"
          aria-label="Search"
        />
        <button
          className="clear-search"
          aria-label="Clear search"
          type="button"
          onClick={() => setSearchTerm('')}
        >
          <span className="visually-hidden">Clear search</span>
        </button>
      </div>

      <button
        type="submit"
        className="btn btn-primary search-btn btn-block d-flex align-items-center align-content-center mt-2 mt-md-0"
        data-ga-category="Search Page"
        data-ga-action="Search Button"
        data-ga-label="n/a"
      >
        Search
      </button>
    </form>
  );
}

/* ---------------------------
   Main Controller
---------------------------- */

function SearchController({restUrl}) {
  const rootsRef = useRef({});

  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [foundPosts, setFoundPosts] = useState(0);
  const [postsPerLoad, setPostsPerLoad] = useState(5);
  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState([]);
  const [postTypes, setpostTypes] = useState([]);
  const [actionTypes, setActionTypes] = useState([]);
  const [contentTypes, setContentTypes] = useState([]);

  // Fetch the filters (categories, post types, etc.):
  const fetchFilters = async () => {
    const params = new URLSearchParams();
    params.set('s', searchTerm);

    const url = `${restUrl}${API_SEARCH.terms}?${params.toString()}`;
    const res = await fetch(url, {
      headers: {'X-Requested-With': 'XMLHttpRequest'},
    });

    const data = await res.json();

    setCategories(data.categories);
    setpostTypes(data.p4_page_type);
    setActionTypes(data.action_type);
    setContentTypes(data.post_types);
  };

  // Render the search results:
  const fetchResults = useCallback(
    async (page = 1, append = false, filters = {}, callback, explicitSearchTerm = null) => {
      const wrapper = document.getElementById('search-results-wrapper');
      const resultsContainer = document.querySelector('#search-results .list-unstyled');

      if (!wrapper || !resultsContainer) {return;}

      const term = explicitSearchTerm ?? searchTerm;
      setLoading(true);

      const params = new URLSearchParams();
      if (term) {
        params.set('s', term);
      }
      params.set('paged', page);

      if (Object.keys(filters).length) {
        params.set(filters.name, filters.value);
      }

      const url = `${restUrl}${API_SEARCH.posts}?${params.toString()}`;
      const res = await fetch(url, {
        headers: {'X-Requested-With': 'XMLHttpRequest'},
      });

      const data = await res.json();
      const html = data.html.replace(/\n/g, '');

      resultsContainer.innerHTML = append ?
        resultsContainer.innerHTML + html :
        html;

      setSearchTerm(term);
      setCurrentPage(data.current_page);
      setFoundPosts(data.found_posts);
      setPostsPerLoad(data.posts_per_load || 5);

      history.pushState({}, '', `?${params.toString()}`);
      setLoading(false);

      if (callback && typeof callback === 'function') {
        callback(data);
      }
    },
    [restUrl, searchTerm]
  );

  // Populate the search results list when the filters are selected:
  // const onFilter = (e, li) => {
  //   if (e.target.tagName === 'INPUT') {return;} // Prevent double toggling if the user clicked the <input> directly

  //   const checkbox = li.querySelector('input[type="checkbox"]');
  //   if (!checkbox) {return;}

  //   checkbox.checked = !checkbox.checked;

  //   const name = checkbox.name;
  //   const value = checkbox.value;

  //   fetchResults(1, false, {name, value});
  // };

  // Show more results when the Load More button is clicked:
  const onLoadMore = useCallback(() => {
    if (loading) {return;}
    const nextPage = currentPage + 1;
    fetchResults(nextPage, true);
  }, [loading, currentPage, fetchResults]);

  // Populate the search results list when the Search button is clicked:
  const onSubmit = useCallback(e => {
    e.preventDefault();
    fetchResults(1, false);
    fetchFilters();
  });

  // Render the categories filter component:
  useEffect(() => {
    rootsRef.current.categories?.render(
      <CategoriesFilter loading={loading} categories={categories} />
    );
  }, [loading, categories]);

  // Render the content types filter component:
  useEffect(() => {
    rootsRef.current.contentTypes?.render(
      <ContentTypesFilter loading={loading} contentTypes={contentTypes} />
    );
  }, [loading, contentTypes]);

  // // Render the post types filter component:
  useEffect(() => {
    rootsRef.current.postTypes?.render(
      <PostTypesFilter loading={loading} postTypes={postTypes} />
    );
  }, [loading, postTypes]);

  // // Render the action types filter component:
  useEffect(() => {
    rootsRef.current.actionTypes?.render(
      <ActionTypesFilter loading={loading} actionTypes={actionTypes} />
    );
  }, [loading, actionTypes]);

  // // Render the load more button component:
  useEffect(() => {
    rootsRef.current.loadMoreButton?.render(
      <LoadMoreButton
        foundPosts={foundPosts}
        currentPage={currentPage}
        postsPerLoad={postsPerLoad}
        onLoadMore={onLoadMore}
      />
    );
  }, [foundPosts, currentPage, postsPerLoad, onLoadMore]);

  // // Render the search title component:
  useEffect(() => {
    rootsRef.current.searchTitle?.render(
      <SearchTitle foundPosts={foundPosts} searchTerm={searchTerm} />
    );
  }, [foundPosts, searchTerm]);

  // // Render the sort filter component:
  useEffect(() => {
    rootsRef.current.sortFilter?.render(
      <SortFilter foundPosts={foundPosts}/>
    );
  }, [foundPosts]);

  // // Render the search form component:
  useEffect(() => {
    const container = document.getElementById('search-bar');
    const siteUrl = container.dataset.siteUrl;

    rootsRef.current.searchForm?.render(
      <SearchForm
        searchTerm={searchTerm}
        setSearchTerm={setSearchTerm}
        siteUrl={siteUrl}
        onSubmit={onSubmit}
      />
    );
  }, [onSubmit, searchTerm]);

  // Create external roots for the components:
  useEffect(() => {
    Object.entries(ROOT_CONFIG).forEach(([key, selector]) => {
      const element = document.querySelector(selector);

      if (element && !rootsRef.current[key]) {
        rootsRef.current[key] = createRoot(element);
      }
    });
  }, []);

  // Get the search term from the URL parameters:
  useEffect(() => {
    const queryString = window.location.search;
    const params = new URLSearchParams(queryString);
    const searchTermParam = params.get('s');

    setSearchTerm(searchTermParam);
  }, []);

  // Populate the search results list:
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const searchTermParam = params.get('s') || '';

    setSearchTerm(searchTermParam);

    fetchResults(1, false, {}, null, searchTermParam);
    fetchFilters(searchTermParam);
  }, []);

  return null;
}

/* ---------------------------
   Render Main Controller
---------------------------- */

const el = document.getElementById('search-controller');
if (el) {
  createRoot(el).render(
    <SearchController restUrl={el.dataset.restUrl} />
  );
}
