import {__} from '@wordpress/i18n';
import {useCallback, useState, useEffect, useRef, createPortal} from '@wordpress/element';
import {fetchJson} from '../../../functions/fetchJson';
import {addQueryArgs} from '../../../functions/addQueryArgs';
import {logDataInSentry} from '../../../functions/logDataInSentry';
import PostItem from '../PostItem';
import Paginator from '../Paginator';
import ListingPageFilters from './ListingPageFilters';
import ListingPageLayoutToggle from './ListingPageLayoutToggle';
import listingPageFilterUrlSync from './listingPageFilterUrlSync';

const PER_PAGE = Number(window.listingPageSettings?.postsPerPage) || 12;

const BASE_URL = document.body.dataset.nro;

const LAYOUTS = {
  STORAGE_NAME: 'layout',
  GRID: 'grid',
  LIST: 'list',
};

/**
 * Reads the current template's archive context from the settings localized by PHP.
 *
 * @return {{postType: string, author: string, tag: string, taxonomy: string, term: string}}
 *   The current archive context, with empty strings for any dimension not applicable to this template.
 */
function getArchiveContext() {
  const settings = window.listingPageSettings || {};

  return {
    postType: settings.archivePostType || '',
    author: settings.archiveAuthor ? String(settings.archiveAuthor) : '',
    tag: settings.archiveTag ? String(settings.archiveTag) : '',
    taxonomy: settings.archiveTaxonomy || '',
    term: settings.archiveTerm ? String(settings.archiveTerm) : '',
  };
}

/**
 * Determines which REST route to query based on the archive context.
 *
 * @param {{postType: string}} archiveContext The current archive context.
 *
 * @return {string} The REST base to query.
 */
function getEndpoint(archiveContext) {
  if (archiveContext.postType || archiveContext.taxonomy === 'action-type') {
    return 'p4_action';
  }
  return 'posts';
}

/**
 * Builds the REST query args imposed by the current archive's context.
 *
 * @param {{author: string, tag: string, taxonomy: string, term: string}} archiveContext The current archive context.
 *
 * @return {Object} Partial REST query args derived from the archive context.
 */
function buildArchiveArgs(archiveContext) {
  const args = {};

  if (archiveContext.author) {
    args.author = archiveContext.author;
  }
  if (archiveContext.tag) {
    args.tags = archiveContext.tag;
  }

  const taxonomyArgMap = {
    category: 'categories',
    'p4-page-type': 'p4-page-type',
    post_tag: 'tags',
    'action-type': 'action-type',
  };
  const taxonomyArgKey = taxonomyArgMap[archiveContext.taxonomy];

  if (taxonomyArgKey && archiveContext.term) {
    args[taxonomyArgKey] = archiveContext.term;
  }

  return args;
}

/**
 * Builds the REST query args from the user's own filter selections.
 *
 * @param {{postType: string, category: string, tag: string}} filters The current filter selections.
 *
 * @return {Object} Partial REST query args derived from the user's filters.
 */
function buildFilterArgs(filters) {
  const args = {};

  if (filters.postType) {
    args['p4-page-type'] = filters.postType;
  }
  if (filters.category) {
    args.categories = filters.category;
  }
  if (filters.tag) {
    args.tags = filters.tag;
  }

  return args;
}

/**
 * Reads the current template's archive context from the settings localized by PHP.
 *
 * @return {{postType: string, author: string, tag: string, taxonomy: string, term: string}}
 *   The current archive context, with empty strings for any dimension not applicable to this template.
 */
function getArchiveContext() {
  const settings = window.listingPageSettings || {};

  return {
    postType: settings.archivePostType || '',
    author: settings.archiveAuthor ? String(settings.archiveAuthor) : '',
    tag: settings.archiveTag ? String(settings.archiveTag) : '',
    taxonomy: settings.archiveTaxonomy || '',
    term: settings.archiveTerm ? String(settings.archiveTerm) : '',
  };
}

/**
 * Determines which REST route to query based on the archive context.
 *
 * @param {{postType: string}} archiveContext The current archive context.
 *
 * @return {string} The REST base to query.
 */
function getEndpoint(archiveContext) {
  if (archiveContext.postType || archiveContext.taxonomy === 'action-type') {
    return 'p4_action';
  }
  return 'posts';
}

/**
 * Renders the dynamic listing page.
 *
 * @param {Object}      props                         Component props.
 * @param {HTMLElement} [props.filtersContainer]      DOM node to portal the filter controls into, if present.
 * @param {HTMLElement} [props.layoutToggleContainer] DOM node to portal the layout toggle button into, if present.
 *
 * @return {JSX.Element} The rendered listing page posts section.
 */
const ListingPagePosts = ({filtersContainer, layoutToggleContainer}) => {
  const [posts, setPosts] = useState([]);
  const [isLoadingPosts, setIsLoadingPosts] = useState(true);
  const [taxonomiesLoaded, setTaxonomiesLoaded] = useState(false);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  const [taxonomies, setTaxonomies] = useState({
    postTypes: [],
    categories: [],
    tags: [],
  });

  const [filters, setFilters] = listingPageFilterUrlSync({
    postTypes: taxonomies.postTypes,
    categories: taxonomies.categories,
    tags: taxonomies.tags,
    loaded: taxonomiesLoaded,
  });

  const [layout, setLayout] = useState(() => {
    try {
      const stored = localStorage.getItem(LAYOUTS.STORAGE_NAME);
      return stored === LAYOUTS.GRID ? LAYOUTS.GRID : LAYOUTS.LIST;
    } catch (e) {
      logDataInSentry(e);
      return LAYOUTS.LIST;
    }
  });

  // Tracks the most recently fired getPosts request, so an earlier
  // request can't overwrite a later correctly filtered result.
  const requestIdRef = useRef(0);

  /**
   * Toggles the layout between grid and list, persisting the new value to `localStorage`.
   *
   * @return {void}
   */
  const handleToggle = () => {
    const newLayout = layout === LAYOUTS.GRID ? LAYOUTS.LIST : LAYOUTS.GRID;
    setLayout(newLayout);

    try {
      localStorage.setItem(LAYOUTS.STORAGE_NAME, newLayout);
    } catch (e) {
      logDataInSentry(e);
    }
  };

  /**
   * Fetches the available post types, categories, and tags, and marks
   * taxonomies as loaded once the request is completed.
   *
   * @return {Promise<void>}
   */
  const getTaxonomies = useCallback(async () => {
    try {
      if (!BASE_URL) {
        logDataInSentry('ListingPagePosts: missing document.body.dataset.nro in getTaxonomies');
      }

      if (!BASE_URL && typeof Sentry !== 'undefined') {
        // eslint-disable-next-line no-undef
        Sentry.captureMessage('ListingPagePosts: missing document.body.dataset.nro in getTaxonomies');
      }

      const [postTypesRes, categoriesRes, tagsRes] = await Promise.all([
        fetchJson(`${BASE_URL}/wp-json/${addQueryArgs('wp/v2/p4-page-type', {per_page: 100, hide_empty: true})}`),
        fetchJson(`${BASE_URL}/wp-json/${addQueryArgs('wp/v2/categories', {per_page: 100, hide_empty: true})}`),
        fetchJson(`${BASE_URL}/wp-json/${addQueryArgs('wp/v2/tags', {per_page: 100, hide_empty: true})}`),
      ]);

      if (!Array.isArray(postTypesRes.data)) {
        logDataInSentry('ListingPagePosts: unexpected post-types response', {extra: {response: postTypesRes}});
      }
      if (!Array.isArray(categoriesRes.data)) {
        logDataInSentry('ListingPagePosts: unexpected categories response', {extra: {response: categoriesRes}});
      }
      if (!Array.isArray(tagsRes.data)) {
        logDataInSentry('ListingPagePosts: unexpected tags response', {extra: {response: tagsRes}});
      }

      setTaxonomies({
        postTypes: Array.isArray(postTypesRes.data) ? postTypesRes.data : [],
        categories: Array.isArray(categoriesRes.data) ? categoriesRes.data : [],
        tags: Array.isArray(tagsRes.data) ? tagsRes.data : [],
      });
    } catch (e) {
      logDataInSentry(e);
    } finally {
      setTaxonomiesLoaded(true);
    }
  }, []);

  /**
   * Fetches posts (or the relevant custom post type) for the current page.
   * Stamps each call with an incrementing request id and ignores the
   * response if a newer request has since been fired.
   *
   * @return {Promise<void>}
   */
  const getPosts = useCallback(async () => {
    const requestId = ++requestIdRef.current;
    setIsLoadingPosts(true);

    try {
      const archiveContext = getArchiveContext();
      const endpoint = getEndpoint(archiveContext);

      const args = {
        per_page: PER_PAGE,
        page,
        _embed: true,
        ...buildArchiveArgs(archiveContext),
        ...buildFilterArgs(filters),
      };

      if (!BASE_URL) {
        logDataInSentry('ListingPagePosts: missing document.body.dataset.nro in getPosts');
      }

      if (!BASE_URL && typeof Sentry !== 'undefined') {
        // eslint-disable-next-line no-undef
        Sentry.captureMessage('ListingPagePosts: missing document.body.dataset.nro in getPosts');
      }

      const {data, totalPages: pages} = await fetchJson(
        `${BASE_URL}/wp-json/wp/v2/${addQueryArgs(endpoint, args)}`
      );

      // Ignore this response if a newer request has been fired.
      if (requestId !== requestIdRef.current) {
        logDataInSentry('Discarded stale getPosts response');
        return;
      }

      if (!Array.isArray(data)) {
        logDataInSentry('ListingPagePosts: unexpected posts response', {extra: {data}});
      }
      if ((typeof pages !== 'number' || Number.isNaN(pages))) {
        logDataInSentry('ListingPagePosts: unexpected totalPages value', {extra: {pages}});
      }

      setPosts(Array.isArray(data) ? data : []);
      setTotalPages(pages);
    } catch (e) {
      logDataInSentry(e);
    } finally {
      if (requestId === requestIdRef.current) {
        setIsLoadingPosts(false);
      }
    }
  }, [filters, page]);

  /**
   * Reset to page 1 whenever filters change.
   *
   * @param {Object} newFilters The new filters.
   */
  const handleApply = newFilters => {
    setFilters(newFilters);
    setPage(1);
  };

  /**
   * Triggers the initial fetch of taxonomies used to populate the filter dropdowns.
   */
  useEffect(() => {
    getTaxonomies();
  }, [getTaxonomies]);

  /**
   * Re-fetches posts whenever `getPosts` changes identity.
   */
  useEffect(() => {
    getPosts();
  }, [getPosts]);

  return (
    <>
      { filtersContainer &&
          createPortal(
            <ListingPageFilters
              postTypes={taxonomies.postTypes}
              categories={taxonomies.categories}
              tags={taxonomies.tags}
              currentPostType={filters.postType}
              currentCategory={filters.category}
              currentTag={filters.tag}
              onApply={handleApply}
            />,
            filtersContainer
          ) }

      { layoutToggleContainer &&
          createPortal(
            <ListingPageLayoutToggle
              layout={layout}
              onToggle={handleToggle}
            />,
            layoutToggleContainer
          ) }

      { !isLoadingPosts && posts.length > 0 && (
        <div className={`wp-block-query is-layout-flow wp-block-query-is-layout-flow wp-block-query--${layout}`}>
          <ul className="wp-block-post-template">
            { posts.map(post => (
              <PostItem key={post.id} post={post} />
            )) }
          </ul>
        </div>
      ) }

      { !isLoadingPosts && posts.length === 0 && (
        <p className="listing-page-no-posts-found">
          { __('No posts found!', 'planet4-master-theme') }
        </p>
      ) }

      { isLoadingPosts && (
        <p className="listing-page-no-posts-found">
          { __('Loading posts…', 'planet4-master-theme') }
        </p>
      ) }

      <Paginator currentPage={page} totalPages={totalPages} onPageChange={setPage} />
    </>
  );
};

export default ListingPagePosts;
