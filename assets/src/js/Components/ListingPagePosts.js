import {useCallback, useState, useEffect, createPortal} from '@wordpress/element';
import {fetchJson} from '../../functions/fetchJson';
import {addQueryArgs} from '../../functions/addQueryArgs';
import ListingPageFilters from './ListingPageFilters';

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

      const [postTypesData, categoriesData, tagsData] = await Promise.all([
        fetchJson(`${baseUrl}/wp-json/${addQueryArgs('wp/v2/p4-page-type', {per_page: 100, hide_empty: true})}`),
        fetchJson(`${baseUrl}/wp-json/${addQueryArgs('wp/v2/categories', {per_page: 100, hide_empty: true})}`),
        fetchJson(`${baseUrl}/wp-json/${addQueryArgs('wp/v2/tags', {per_page: 100, hide_empty: true})}`),
      ]);

      setPostTypes(Array.isArray(postTypesData) ? postTypesData : []);
      setCategories(Array.isArray(categoriesData) ? categoriesData : []);
      setTags(Array.isArray(tagsData) ? tagsData : []);
    } catch (e) {
      // eslint-disable-next-line no-console
      console.log(e);
    }
  }, []);

  const getPosts = useCallback(async () => {
    try {
      const args = {
        per_page: 3,
        ignore_categories: false,
        offset: 0,
      };

      if (filters.postType) {
        args['p4-page-type'] = filters.postType; // already a number from FilterSelect
      }
      if (filters.category) {
        args.categories = filters.category;
      }
      if (filters.tag) {
        args.tags = filters.tag;
      }

      const baseUrl = document.body.dataset.nro;

      const data = await fetchJson(`${baseUrl}/wp-json/${addQueryArgs('wp/v2/posts', args)}`);

      setPosts(Array.isArray(data) ? data : []);
    } catch (e) {
      // eslint-disable-next-line no-console
      console.log(e);
    }
  }, [filters]);

  useEffect(() => {
    getTaxonomies();
  }, [getTaxonomies]);

  useEffect(() => {
    getPosts();
  }, [getPosts]);

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
            onApply={setFilters}
          />,
          filtersContainer
        ) }

      <ul>
        { posts.map(post => (
          <PostItem key={post.id} post={post} />
        )) }
      </ul>
    </>
  );
};

export default ListingPagePosts;
