import {useCallback, useState, useEffect, useRef, createPortal} from '@wordpress/element';
import {fetchJson} from '../../../functions/fetchJson';
import {addQueryArgs} from '../../../functions/addQueryArgs';
import PostItem from '../PostItem';
import Paginator from '../Paginator';
import ListingPageFilters from './ListingPageFilters';
import ListingPageLayoutToggle from './ListingPageLayoutToggle';

const PER_PAGE = window.listingPageSettings || 3;

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

function getIdBySlug(list, slug) {
  const match = list.find(item => item.slug === slug);
  return match ? match.id : '';
}

function getSlugById(list, id) {
  const match = list.find(item => item.id === id);
  return match ? match.slug : '';
}

const ListingPagePosts = ({filtersContainer, layoutToggleContainer}) => {
  const [posts, setPosts] = useState([]);
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

  const getPosts = useCallback(async () => {
    try {
      const args = {
        per_page: PER_PAGE,
        page,
        _embed: true,
      };

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
        `${baseUrl}/wp-json/${addQueryArgs('wp/v2/posts', args)}`
      );

      setPosts(Array.isArray(data) ? data : []);
      setTotalPages(pages);
    } catch (e) {
      // eslint-disable-next-line no-console
      console.log(e);
    }
  }, [filters, page]);

  // Reset to page 1 whenever filters change, so you don't get stuck on
  // e.g. page 5 of a filtered set that only has 2 pages.
  const handleApply = newFilters => {
    setFilters(newFilters);
    setPage(1);
  };

  useEffect(() => {
    getTaxonomies();
  }, [getTaxonomies]);

  useEffect(() => {
    getPosts();
  }, [getPosts]);

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

  // Read filters from the URL once taxonomies have loaded (slugs in the
  // URL need to be converted to the numeric IDs the API expects).
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

  // Keep the URL in sync whenever filters change (converting IDs back
  // to slugs for a readable, shareable URL).
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

      { posts.length > 0 && (
        <div className={`wp-block-query is-layout-flow wp-block-query-is-layout-flow wp-block-query--${layout}`}>
          <ul className="wp-block-post-template">
            { posts.map(post => (
              <PostItem key={post.id} post={post} />
            )) }
          </ul>
        </div>
      ) }

      { posts.length === 0 && (
        <p className="listing-page-no-posts-found">
					No posts found!
        </p>
      ) }

      <Paginator currentPage={page} totalPages={totalPages} onPageChange={setPage} />
    </>
  );
};

export default ListingPagePosts;
