import {createRoot, useEffect, useState, useRef, useCallback} from '@wordpress/element';

function CategoriesFilter({loading, categories = {}}) {
  if (loading) {
    return <div className="search-meta">Loading…</div>;
  }

  const categoryList = Object.values(categories);

  return (
    <ul className="list-unstyled">
      {categoryList.map(category => {
        const count = category.results ?? category.count ?? 0;

        const ariaLabel =
          count === 1 ?
            `Filter results by category ${category.name}, 1 result was found` :
            `Filter results by category ${category.name}, ${count} results were found`;

        return (
          <li key={category.id}>
            {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
            <label className="custom-control">
              <input
                type="checkbox"
                name={`f[cat][${category.name}]`}
                value={category.id}
                className="p4-custom-control-input"
                data-ga-category="Search Page"
                data-ga-action="Category Filter"
                data-ga-label={category.name}
                aria-label={ariaLabel}
              />
              <span className="custom-control-description">
                {category.name} {count > 0 && `(${count})`}
              </span>
            </label>
          </li>
        );
      })}
    </ul>
  );
}

function ContentTypesFilter({loading, contentTypes = {}}) {
  if (loading) {
    return <div className="search-meta">Loading…</div>;
  }

  const contentTypesList = Object.values(contentTypes);

  return (
    <ul className="list-unstyled">
      {contentTypesList.map(type => {
        const count = type.results ?? type.count ?? 0;

        const ariaLabel =
          count === 1 ?
            `Filter results by post type ${type.slug}, 1 result was found` :
            `Filter results by post type ${type.slug}, ${count} results were found`;

        return (
          <li key={type.slug}>
            {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
            <label className="custom-control">
              <input
                type="checkbox"
                name={`f[ptype][${type.slug}]`}
                value={type.id}
                className="p4-custom-control-input"
                data-ga-category="Search Page"
                data-ga-action="Content Type Filter"
                data-ga-label={type.slug}
                aria-label={ariaLabel}
              />
              <span className="custom-control-description">
                {type.slug} {count > 0 && `(${count})`}
              </span>
            </label>
          </li>
        );
      })}
    </ul>
  );
}

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

function SearchController({restUrl}) {
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [foundPosts, setFoundPosts] = useState(0);
  const [postsPerLoad, setPostsPerLoad] = useState(5);
  // const [postsData, setPostsData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState([]);
  // const [postTypes, setpostTypes] = useState([]);
  // const [actionTypes, setActionTypes] = useState([]);
  const [contentTypes, setContentTypes] = useState([]);

  const metaRootRef = useRef(null);
  const contentTypesFilterRootRef = useRef(null);
  const loadMoreButtonRef = useRef(null);
  const searchTitleRef = useRef(null);

  const fetchFilters = async () => {
    const input = document.getElementById('search-page-input');

    const params = new URLSearchParams();
    params.set('s', input.value);

    const apiRoute = 'planet4/v1/search-taxonomies';
    const url = `${restUrl}${apiRoute}?${params.toString()}`;
    const res = await fetch(url, {
      headers: {'X-Requested-With': 'XMLHttpRequest'},
    });

    const data = await res.json();

    // console.log(data);

    setCategories(data.categories);
    // setpostTypes(data.p4_page_type);
    // setActionTypes([]);
    setContentTypes(data.post_types);
  };

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

    const apiRoute = 'planet4/v1/search';
    const url = `${restUrl}${apiRoute}?${params.toString()}`;
    const res = await fetch(url, {
      headers: {'X-Requested-With': 'XMLHttpRequest'},
    });

    const data = await res.json();
    const html = data.html.replace(/\n/g, '');

    resultsContainer.innerHTML = append ?
      resultsContainer.innerHTML + html :
      html;

    // setPostsData(data.posts);
    setSearchTerm(input.value);
    setCurrentPage(data.current_page);
    setFoundPosts(data.found_posts);
    setPostsPerLoad(data.posts_per_load || 5);

    history.pushState({}, '', `?${params.toString()}`);
    setLoading(false);
  };

  // Populate the search results list when the Search button is clicked:
  const onSubmit = e => {
    e.preventDefault();
    fetchResults(1, false);
    fetchFilters();
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

  // Show more results when the Load More button is clicked:
  const onLoadMore = useCallback(() => {
    if (loading) {return;}
    const nextPage = currentPage + 1;
    fetchResults(nextPage, true);
  }, [loading, currentPage]);

  // Create external root once
  useEffect(() => {
    const categoriesFilter = document.querySelector('#item-issue');
    const contentTypesFilter = document.querySelector('#item-content');
    const searchTitle = document.querySelector('#result-statement');
    const loadMoreButton = document.querySelector('.load-more-button-div');

    if (categoriesFilter && !metaRootRef.current) {
      metaRootRef.current = createRoot(categoriesFilter);
    }
    if (contentTypesFilter && !contentTypesFilterRootRef.current) {
      contentTypesFilterRootRef.current = createRoot(contentTypesFilter);
    }
    if (loadMoreButton && !loadMoreButtonRef.current) {
      loadMoreButtonRef.current = createRoot(loadMoreButton);
    }
    if (searchTitle && !searchTitleRef.current) {
      searchTitleRef.current = createRoot(searchTitle);
    }
  }, []);

  // Render external component whenever relevant state changes
  useEffect(() => {
    if (!metaRootRef.current) {return;}

    metaRootRef.current.render(
      <CategoriesFilter
        loading={loading}
        categories={categories}
      />
    );
  }, [loading, categories]);

  useEffect(() => {
    if (!contentTypesFilterRootRef.current) {return;}

    contentTypesFilterRootRef.current.render(
      <ContentTypesFilter
        loading={loading}
        contentTypes={contentTypes}
      />
    );
  }, [loading, contentTypes]);

  useEffect(() => {
    if (!loadMoreButtonRef.current) {return;}

    loadMoreButtonRef.current.render(
      <LoadMoreButton
        foundPosts={foundPosts}
        currentPage={currentPage}
        postsPerLoad={postsPerLoad}
        onLoadMore={onLoadMore}
      />
    );
  }, [foundPosts, currentPage, postsPerLoad, onLoadMore]);

  useEffect(() => {
    if (!searchTitleRef.current) {return;}

    searchTitleRef.current.render(
      <SearchTitle
        foundPosts={foundPosts}
        searchTerm={searchTerm}
      />
    );
  }, [foundPosts, searchTerm]);

  // Populate the search results list on component mount:
  useEffect(() => {
    fetchResults(1, false);
    fetchFilters();
  }, []);

  // Add and remove listeners:
  useEffect(() => {
    const form = document.getElementById('search_form_inner');
    const filters = document.querySelectorAll('#filter-sidebar-options .filteritem li');

    if (form) {
      form.addEventListener('submit', onSubmit);
    }
    if (filters) {
      filters.forEach(filter => {
        filter.addEventListener('click', e => onFilter(e, filter));
      });
    }

    return () => {
      if (form) {
        form.removeEventListener('submit', onSubmit);
      }
    };
  });

  return null;
}

// Mount the component
const el = document.getElementById('search-controller');
if (el) {
  createRoot(el).render(
    <SearchController restUrl={el.dataset.restUrl} />
  );
}
