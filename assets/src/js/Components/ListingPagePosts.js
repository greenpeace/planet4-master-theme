import {useCallback, useState, useEffect, createPortal} from '@wordpress/element';
import {fetchJson} from '../../functions/fetchJson';
import {addQueryArgs} from '../../functions/addQueryArgs';
import ListingPageFilters from './ListingPageFilters';
import Paginator from './Paginator';

const PER_PAGE = 3;

const ListingPagePosts = ({filtersContainer}) => {
  const [posts, setPosts] = useState([]);
  const [postTypes, setPostTypes] = useState([]);
  const [categories, setCategories] = useState([]);
  const [tags, setTags] = useState([]);
  const [filters, setFilters] = useState({
    postType: '',
    category: '',
    tag: '',
  });
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  function PostItem({post}) {
    return (
      <li>
        <h3 dangerouslySetInnerHTML={{__html: post.title.rendered}} />
        <div dangerouslySetInnerHTML={{__html: post.excerpt.rendered}} />
      </li>
    );
  }

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
    }
  }, []);

  const getPosts = useCallback(async () => {
    try {
      const args = {
        per_page: PER_PAGE,
        page,
        ignore_categories: false,
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

  useEffect(() => {
    getTaxonomies();
  }, [getTaxonomies]);

  useEffect(() => {
    getPosts();
  }, [getPosts]);

  // Reset to page 1 whenever filters change, so you don't get stuck on
  // e.g. page 5 of a filtered set that only has 2 pages.
  const handleApply = newFilters => {
    setFilters(newFilters);
    setPage(1);
  };

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

      <ul>
        { posts.map(post => (
          <PostItem key={post.id} post={post} />
        )) }
      </ul>

      <Paginator currentPage={page} totalPages={totalPages} onPageChange={setPage} />
    </>
  );
};

export default ListingPagePosts;
