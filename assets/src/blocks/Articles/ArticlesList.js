import { ArticlePreview } from './ArticlePreview';

export const ArticlesList = (props) => {
  const { posts, postType } = props;

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
