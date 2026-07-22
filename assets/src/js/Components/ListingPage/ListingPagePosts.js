import {__} from '@wordpress/i18n';
import {useCallback, useState, useEffect, useRef, createPortal} from '@wordpress/element';
import {fetchJson} from '../../../functions/fetchJson';
import {addQueryArgs} from '../../../functions/addQueryArgs';
import PostItem from '../PostItem';
import Paginator from '../Paginator';
import ListingPageFilters from './ListingPageFilters';
import ListingPageLayoutToggle from './ListingPageLayoutToggle';

const PER_PAGE = Number(window.listingPageSettings?.postsPerPage) || 12;

const LAYOUTS = {
  STORAGE_NAME: 'layout',
  GRID: 'grid',
  LIST: 'list',
};

const URL_PARAMS = {
  postType: 'post-type',
  category: 'category',
  tag: 'tag',
};

/**
 * Finds a term's id by its slug within a list of terms.
 *
 * @param {Array<{id: number, slug: string}>} list List of terms to search.
 * @param {string}                            slug The slug to look up.
 *
 * @return {number|string} The matching term's id, or `''` if not found.
 */
function getIdBySlug(list, slug) {
  const match = list.find(item => item.slug === slug);
  return match ? match.id : '';
}

/**
 * Finds a term's slug by its id within a list of terms.
 *
 * @param {Array<{id: number, slug: string}>} list List of terms to search.
 * @param {number}                            id   The id to look up.
 *
 * @return {string} The matching term's slug, or `''` if not found.
 */
function getSlugById(list, id) {
  const match = list.find(item => item.id === id);
  return match ? match.slug : '';
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
  const [postTypes, setPostTypes] = useState([]);
  const [categories, setCategories] = useState([]);
  const [tags, setTags] = useState([]);
  const [taxonomiesLoaded, setTaxonomiesLoaded] = useState(false);
  const [layout, setLayout] = useState(LAYOUTS.LIST);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [filters, setFilters] = useState({
    postType: '',
    category: '',
    tag: '',
  });

  const hasSyncedFromUrl = useRef(false);

  // Tracks the most recently fired getPosts request, so an earlier
  // request can't overwrite a later correctly filtered result.
  const requestIdRef = useRef(0);

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
      if (typeof Sentry !== 'undefined') {
        // eslint-disable-next-line no-undef
        Sentry.captureException(e);
      }
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
      const baseUrl = document.body.dataset.nro;

      const [postTypesRes, categoriesRes, tagsRes] = await Promise.all([
        fetchJson(`${baseUrl}/wp-json/${addQueryArgs('wp/v2/p4-page-type', {per_page: 100, hide_empty: true})}`),
        fetchJson(`${baseUrl}/wp-json/${addQueryArgs('wp/v2/categories', {per_page: 100, hide_empty: true})}`),
        fetchJson(`${baseUrl}/wp-json/${addQueryArgs('wp/v2/tags', {per_page: 100, hide_empty: true})}`),
      ]);

      setPostTypes(Array.isArray(postTypesRes.data) ? postTypesRes.data : []);
      setCategories(Array.isArray(categoriesRes.data) ? categoriesRes.data : []);
      setTags(Array.isArray(tagsRes.data) ? tagsRes.data : []);
    } catch (e) {
      // eslint-disable-next-line no-console
      console.log(e);
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
      };

      // Fixed constraint from the current archive (author.php, tag.php,
      // taxonomy.php) — applies regardless of the user's own filter picks.
      if (archiveContext.author) {
        args.author = archiveContext.author;
      }
      if (archiveContext.tag) {
        args.tags = archiveContext.tag;
      }
      if (archiveContext.taxonomy === 'category' && archiveContext.term) {
        args.categories = archiveContext.term;
      }
      if (archiveContext.taxonomy === 'p4-page-type' && archiveContext.term) {
        args['p4-page-type'] = archiveContext.term;
      }
      if (archiveContext.taxonomy === 'post_tag' && archiveContext.term) {
        args.tags = archiveContext.term;
      }
      if (archiveContext.taxonomy === 'action-type' && archiveContext.term) {
        args['action-type'] = archiveContext.term;
      }

      // User-selected filters layer on top, further narrowing within
      // the archive's fixed context. They override the same key if both
      // happen to target it (e.g. picking a tag filter while already on
      // a taxonomy.php page for a different taxonomy).
      if (filters.postType) {
        args['p4-page-type'] = filters.postType;
      }
      if (filters.category) {
        args.categories = filters.category;
      }
      if (filters.tag) {
        args.tags = filters.tag;
      }

      const baseUrl = document.body.dataset.nro;

      const {data, totalPages: pages} = await fetchJson(
        `${baseUrl}/wp-json/wp/v2/${addQueryArgs(endpoint, args)}`
      );

      // Ignore this response if a newer request has been fired.
      if (requestId !== requestIdRef.current) {
        return;
      }

      setPosts(Array.isArray(data) ? data : []);
      setTotalPages(pages);
    } catch (e) {
      // eslint-disable-next-line no-console
      console.log(e);
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

  /**
   * Restores a previously saved layout preference from `localStorage` on mount.
   */
  useEffect(() => {
    try {
      const stored = localStorage.getItem(LAYOUTS.STORAGE_NAME);
      if (stored === LAYOUTS.GRID || stored === LAYOUTS.LIST) {
        setLayout(stored);
      }
    } catch (e) {
      if (typeof Sentry !== 'undefined') {
        // eslint-disable-next-line no-undef
        Sentry.captureException(e);
      }
    }
  }, []);

  /**
   * Reads filters from the URL once taxonomies have loaded.
   * Only applies on the main posts page.
   */
  useEffect(() => {
    if (hasSyncedFromUrl.current || !taxonomiesLoaded) {
      return;
    }

    const params = new URLSearchParams(window.location.search);
    const postTypeSlug = params.get(URL_PARAMS.postType);
    const categorySlug = params.get(URL_PARAMS.category);
    const tagSlug = params.get(URL_PARAMS.tag);

    const urlFilters = {
      postType: postTypeSlug ? getIdBySlug(postTypes, postTypeSlug) : '',
      category: categorySlug ? getIdBySlug(categories, categorySlug) : '',
      tag: tagSlug ? getIdBySlug(tags, tagSlug) : '',
    };

    if (urlFilters.postType || urlFilters.category || urlFilters.tag) {
      setFilters(urlFilters);
    }

    hasSyncedFromUrl.current = true;
  }, [taxonomiesLoaded, postTypes, categories, tags]);

  /**
   * Keeps the URL in sync whenever filters change.
   */
  useEffect(() => {
    if (!taxonomiesLoaded) {
      return;
    }

    const params = new URLSearchParams(window.location.search);

    const postTypeSlug = filters.postType ? getSlugById(postTypes, filters.postType) : '';
    const categorySlug = filters.category ? getSlugById(categories, filters.category) : '';
    const tagSlug = filters.tag ? getSlugById(tags, filters.tag) : '';

    [
      [URL_PARAMS.postType, postTypeSlug],
      [URL_PARAMS.category, categorySlug],
      [URL_PARAMS.tag, tagSlug],
    ].forEach(([key, value]) => {
      if (value) {
        params.set(key, value);
      } else {
        params.delete(key);
      }
    });

    const queryString = params.toString();
    const newUrl = `${window.location.pathname}${queryString ? `?${queryString}` : ''}${window.location.hash}`;

    window.history.pushState(null, '', newUrl);
  }, [filters, taxonomiesLoaded, postTypes, categories, tags]);

  return (
    <>
      { filtersContainer &&
        createPortal(
          <ListingPageFilters
            postTypes={postTypes}
            categories={categories}
            tags={tags}
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
