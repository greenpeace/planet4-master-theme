import {createRoot, useEffect, useState, useRef, useCallback} from '@wordpress/element';

/* ---------------------------
   Constants
---------------------------- */

const API_SEARCH = {
  posts: 'planet4/v1/search',
  terms: 'planet4/v1/search-taxonomies',
};

const ROOT_CONFIG = {
  categories: '#item-issue',
  contentTypes: '#item-content',
  postTypes: '#item-post-types',
  actionTypes: '#item-action',
  categoriesModal: '#item-modal-issue',
  contentTypesModal: '#item-modal-content',
  postTypesModal: '#item-modal-post-types',
  actionTypesModal: '#item-modal-action',
  searchTitle: '#result-statement',
  searchResult: '#search-results',
  searchForm: '#search-bar',
  sortFilter: '#sort-filter',
  loadMoreButton: '.load-more-button-div',
};

const baseCategoryFilter = {
  stateKey: 'categories',
  ariaSubject: 'category',
  namespace: 'cat',
  gaAction: 'Category Filter',
  getKey: item => item.id,
  getLabel: item => item.name,
};

const baseContentTypeFilter = {
  stateKey: 'contentTypes',
  ariaSubject: 'content type',
  namespace: 'ctype',
  gaAction: 'Content Type Filter',
  getKey: item => item.slug,
  getLabel: item => item.slug,
};

const basePostTypeFilter = {
  stateKey: 'postTypes',
  ariaSubject: 'post type',
  namespace: 'ptype',
  gaAction: 'Post Type Filter',
  getKey: item => item.id,
  getLabel: item => item.name,
};

const baseActionTypeFilter = {
  stateKey: 'actionTypes',
  ariaSubject: 'action type',
  namespace: 'atype',
  gaAction: 'Action Type Filter',
  getKey: item => item.id,
  getLabel: item => item.name,
};

const FILTER_ROOTS = [
  {...baseCategoryFilter, rootKey: 'categories'},
  {...baseCategoryFilter, rootKey: 'categoriesModal'},
  {...baseContentTypeFilter, rootKey: 'contentTypes'},
  {...baseContentTypeFilter, rootKey: 'contentTypesModal'},
  {...basePostTypeFilter, rootKey: 'postTypes'},
  {...basePostTypeFilter, rootKey: 'postTypesModal'},
  {...baseActionTypeFilter, rootKey: 'actionTypes'},
  {...baseActionTypeFilter, rootKey: 'actionTypesModal'},
];

/* ---------------------------
   Components
---------------------------- */

// Filters List component:
function FilterList({
  loading,
  items = {},
  filterNamespace,
  gaAction,
  getLabel,
  getAriaSubject,
  setAppliedFilters,
  appliedFilters,
}) {
  const list = Object.values(items);

  if (loading) {
    return (
      <div>Loading...</div>
    );
  }
  return (
    <ul className="list-unstyled">
      {list.map(item => {
        const count = item.results ?? item.count ?? 0;
        const label = getLabel(item);

        const ariaLabel =
          count === 1 ?
            `Filter results by ${getAriaSubject} ${label}, 1 result was found` :
            `Filter results by ${getAriaSubject} ${label}, ${count} results were found`;

        const filter = {
          name: `f[${filterNamespace}][${label}]`,
          value: item.id,
          label,
        };

        return (
          <li key={label}>
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
                defaultChecked={appliedFilters.some(f => f.label === label)}
                onChange={() =>
                  setAppliedFilters(prev => {
                    const exists = prev.find(element => element.value === item.id);
                    if (exists) {
                      return prev.filter(element => element.value !== item.id);
                    }
                    return [...prev, filter];
                  })}
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

// Load More button component:
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

// Sort-by filter component:
function SortFilter({foundPosts, sortMethod, setSortMethod}) {
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
        value={sortMethod}
        onChange={e => setSortMethod(e.target.value)}
      >
        <option value="_score">Most relevant</option>
        <option value="post_date">Newest</option>
        <option value="post_date_asc">Oldest</option>
      </select>
    </div>
  );
}

// Search title component:
function SearchTitle({foundPosts, loading}) {
  const params = new URLSearchParams(window.location.search);
  const searchTermParam = params.get('s') || '';

  if (loading) {
    return (
      <div>Loading...</div>
    );
  }
  return (
    <>
      <h1 className="result-statement">
        {foundPosts} results for {searchTermParam}
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

// Search results component:
function SearchResult({posts, loading}) {
  if (loading) {
    return (
      <div>Loading...</div>
    );
  }
  return (
    <ul className="list-unstyled">
      {posts.map(post => {
        return (
          <div key={post.id} className="search-results-load">
            <li id="result-row" className="d-flex search-result-list-item">
              <a className="d-flex search-result-item-image" href={post.link} data-ga-category="Search Results" data-ga-action="Image" data-ga-label="Post" tabIndex="-1">
                {post?.featured_image?.url && (<img src={post.featured_image.url} loading="lazy" alt="" role="presentation" />)}
              </a>
              <div className="search-result-item-body tease tease-post">
                <div className="search-result-item-flex-title">
                  <div className="d-flex flex-column-reverse">
                    <h4>
                      <a href={post.link} data-ga-category="Search Results" className="search-result-item-headline" data-ga-action="Title" data-ga-label="Post">
                        {post.title}
                      </a>
                    </h4>
                  </div>
                  <div>
                  </div>
                </div>
                <p className="search-result-item-content">
                  {post.excerpt}
                </p>
                <div className="search-result-item-info">
                  <span className="search-result-item-date">
                    {post.date}
                  </span>
                </div>
              </div>
            </li>
          </div>
        );
      })}
    </ul>
  );
}

// Search form component:
function SearchForm({setSearchTerm, siteUrl, searchTerm}) {
  return (
    <form
      id="search_form_inner"
      method="get"
      role="search"
      className="form d-md-flex"
      action={siteUrl}
      onSubmit={e => {
        e.preventDefault();
        setSearchTerm(e.currentTarget.elements.s.value);
      }}
    >
      <div className="search-input-container w-100">
        <input
          type="search"
          id="search-page-input"
          className="form-control"
          placeholder="Search by name, keyword, or topic"
          defaultValue={searchTerm}
          name="s"
          aria-label="Search"
        />
        <button
          className="clear-search"
          aria-label="Clear search"
          type="button"
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

  const [posts, setPosts] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [foundPosts, setFoundPosts] = useState(0);
  const [postsPerLoad, setPostsPerLoad] = useState(5);
  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState([]);
  const [postTypes, setPostTypes] = useState([]);
  const [actionTypes, setActionTypes] = useState([]);
  const [contentTypes, setContentTypes] = useState([]);
  const [appliedFilters, setAppliedFilters] = useState([]);
  const [sortMethod, setSortMethod] = useState('_score');

  /* ---------------------------
   Main Controller: FUNCTIONS
  ---------------------------- */

  // Helper: fetch JSON from REST endpoint with search params
  const fetchJson = useCallback(
    async (endpoint, paramsObj = {}) => {
      const params = new URLSearchParams(paramsObj);
      const url = `${restUrl}${endpoint}?${params.toString()}`;

      const res = await fetch(url, {
        headers: {'X-Requested-With': 'XMLHttpRequest'},
      });

      if (!res.ok) {
        throw new Error(`Request failed: ${res.status}`);
      }

      return res.json();
    });

  // Fetch filters (categories, post types, etc.)
  const fetchFilters = async (explicitSearchTerm = null) => {
    const term = explicitSearchTerm ?? searchTerm;
    const params = {};

    if (term) {params.s = term;}

    appliedFilters.forEach(filter => {
      params[filter.name] = filter.value;
    });

    const data = await fetchJson(API_SEARCH.terms, params);

    setCategories(data.categories);
    setPostTypes(data.p4_page_type);
    setActionTypes(data.action_type);
    setContentTypes(data.post_types);
  };

  // Fetch search results
  const fetchResults = useCallback(
    async (page = 1, callback, explicitSearchTerm = null, newSearch = false) => {
      const term = explicitSearchTerm ?? searchTerm;
      setLoading(true);

      const params = {
        paged: page,
        orderby: sortMethod,
      };
      if (term) {
        params.s = term;
      }

      appliedFilters.forEach(filter => {
        params[filter.name] = filter.value;
      });

      const data = await fetchJson(API_SEARCH.posts, params);

      setPosts(prev => (newSearch ? data.posts : [...prev, ...data.posts]));
      // setSearchTerm(term);
      setCurrentPage(data.current_page);
      setFoundPosts(data.found_posts);
      setPostsPerLoad(data.posts_per_load || 5);

      history.pushState({}, '', `?${new URLSearchParams(params).toString()}`);
      setLoading(false);

      if (typeof callback === 'function') {
        callback(data);
      }
    },
    [appliedFilters, fetchJson, searchTerm, sortMethod]
  );

  // Fetch more results when the Load More button is clicked:
  const onLoadMore = useCallback(() => {
    if (loading) {return;}

    fetchResults(currentPage + 1);
  }, [loading, currentPage, fetchResults]);

  /* ---------------------------
   Main Controller: HOOKS
  ---------------------------- */

  // Render the categories filter component:
  useEffect(() => {
    const currentState = {
      categories,
      contentTypes,
      postTypes,
      actionTypes,
    };

    FILTER_ROOTS.forEach(filter => {
      const root = rootsRef.current[filter.rootKey];
      const items = currentState[filter.stateKey] || [];

      if (!root) {return;}

      root.render(
        <FilterList
          loading={loading}
          items={items}
          filterNamespace={filter.namespace}
          gaAction={filter.gaAction}
          getKey={filter.getKey}
          getLabel={filter.getLabel}
          getAriaSubject={filter.ariaSubject}
          appliedFilters={appliedFilters}
          setAppliedFilters={setAppliedFilters}
        />
      );
    });
  }, [loading, categories, contentTypes, postTypes, actionTypes, appliedFilters]);

  // Render the load more button component:
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

  // Render the search title component:
  useEffect(() => {
    rootsRef.current.searchTitle?.render(
      <SearchTitle foundPosts={foundPosts} loading={loading} />
    );
  }, [foundPosts]);

  // Render the search results component:
  useEffect(() => {
    rootsRef.current.searchResult?.render(
      <SearchResult posts={posts} loading={loading} />
    );
  }, [posts]);

  // Render the sort filter component:
  useEffect(() => {
    rootsRef.current.sortFilter?.render(
      <SortFilter
        foundPosts={foundPosts}
        sortMethod={sortMethod}
        setSortMethod={setSortMethod}
      />
    );
  }, [foundPosts, sortMethod]);

  // Render the search form component:
  useEffect(() => {
    const container = document.getElementById('search-bar');
    const siteUrl = container.dataset.siteUrl;

    rootsRef.current.searchForm?.render(
      <SearchForm
        searchTerm={searchTerm}
        setSearchTerm={setSearchTerm}
        siteUrl={siteUrl}
      />
    );
  }, [searchTerm]);

  // Create external roots for the components:
  useEffect(() => {
    Object.entries(ROOT_CONFIG).forEach(([key, selector]) => {
      const element = document.querySelector(selector);

      if (element && !rootsRef.current[key]) {
        rootsRef.current[key] = createRoot(element);
      }
    });
  }, []);

  // Fetch results list on page load:
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const searchTermParam = params.get('s') || '';

    setSearchTerm(searchTermParam);

    fetchResults(1, null, searchTermParam, true);
    fetchFilters(searchTermParam);
  }, []);

  // Fetch results and update the active filters when
  // filters, search terms, or sorting method are modified:
  useEffect(() => {
    fetchResults(1, null, null, true);
    fetchFilters();
  }, [appliedFilters, searchTerm, sortMethod]);

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
