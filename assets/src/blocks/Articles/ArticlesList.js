import { Component } from '@wordpress/element';
import { ArticlePreview } from './ArticlePreview';

const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

export class ArticlesList extends Component {
  constructor(props) {
    super(props);
    this.state = {
      posts: props.posts || []
    };

    this.loadArticles = this.loadArticles.bind(this);
  }

  componentDidMount() {
    this.loadArticles();
  }

  componentDidUpdate(prevProps) {
    const { article_count, tags, posts, post_types, ignore_categories, page } = this.props;
    if (
      article_count !== prevProps.article_count ||
      tags.length !== prevProps.tags.length ||
      post_types.length !== prevProps.post_types.length ||
      ignore_categories !== prevProps.ignore_categories ||
      posts.length !== prevProps.posts.length
    ) {
      this.loadArticles();
    } else if (page !== prevProps.page) {
      this.loadArticles(page);
    }
  }

  async loadArticles(page) {
    const {
      article_count,
      posts,
      tags,
      post_types,
      ignore_categories,
      postType,
      postId,
      setTotalPages
    } = this.props;

    const args = {
      article_count: article_count && article_count > 0 ? article_count : 0,
      post_types,
      posts,
      tags,
      ignore_categories
    };

    if (page) {
      args.offset = page * article_count;
    }

    if (postType === 'post') {
      args.exclude_post_id = postId;
    }

    const queryArgs = {
      path: addQueryArgs('/planet4/v1/get-posts', args)
    };

    try {
      const result = await apiFetch(queryArgs);

      let newPosts = [];
      let totalPages = 0;
      if (result) {
        if (page) {
          newPosts = [...this.state.posts, ...result.recent_posts];
        } else {
          newPosts = result.recent_posts;
          totalPages = result.total_pages;
        }
      }
      this.setState({
        posts: newPosts
      });
      if (totalPages && setTotalPages) {
        setTotalPages(totalPages);
      }
    } catch (error) {
      console.log(error);
    }
  }

  render() {
    const { posts } = this.state;
    const { postType } = this.props;

    return (
      <div className="article-list-section clearfix">
        {posts && posts.length > 0 && posts.map(post =>
          <ArticlePreview
            key={post.post_title}
            isCampaign={postType === 'campaign'}
            post={post}
          />
        )}
      </div>
    );
  }
}
