const {__} = wp.i18n;

export const PostTypesSelect = ({postTypes, onChangePostType, immediate}) => (
  <select className="post-type-select" onChange={evt => onChangePostType(evt, immediate)}>
    <option value="">{__('All post types', 'planet4-master-theme')}</option>
    {postTypes.map(postType => (
      <option key={postType.id} value={postType.id} >{postType.name}</option>
    ))}
  </select>
);
