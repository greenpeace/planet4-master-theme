import {useState, useEffect, useRef} from '@wordpress/element';
import {logDataInSentry} from '../../../functions/logDataInSentry';

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
 * @return {number|string} The matching term's id.
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
 * @return {string} The matching term's slug.
 */
function getSlugById(list, id) {
  const match = list.find(item => item.id === id);
  return match ? match.slug : '';
}

/**
 * Manages the listing page's filter state, keeping it in sync with the
 * URL's query string in both directions:
 *
 * @param {Object}                            params            Hook parameters.
 * @param {Array<{id: number, slug: string}>} params.postTypes  Available post type terms, used to resolve slugs/ids.
 * @param {Array<{id: number, slug: string}>} params.categories Available category terms, used to resolve slugs/ids.
 * @param {Array<{id: number, slug: string}>} params.tags       Available tag terms, used to resolve slugs/ids.
 * @param {boolean}                           params.loaded     Whether the taxonomy lists above have finished loading.
 *
 * @return {[Object, Function]} A `[filters, setFilters]` tuple, matching `useState`'s return shape.
 */
export default function listingPageFilterUrlSync({postTypes, categories, tags, loaded}) {
  const [filters, setFilters] = useState({
    postType: '',
    category: '',
    tag: '',
  });

  const hasSyncedFromUrl = useRef(false);

  /**
   * Reads filters from the URL once taxonomies have loaded. Runs at most
   * once per mount, guarded by `hasSyncedFromUrl`.
   */
  useEffect(() => {
    if (hasSyncedFromUrl.current || !loaded) {
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
  }, [loaded, postTypes, categories, tags]);

  /**
   * Keeps the URL in sync whenever filters change.
   */
  useEffect(() => {
    if (!loaded) {
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

    try {
      window.history.pushState(null, '', newUrl);
    } catch (e) {
      logDataInSentry(e);
    }
  }, [filters, loaded, postTypes, categories, tags]);

  return [filters, setFilters];
}
