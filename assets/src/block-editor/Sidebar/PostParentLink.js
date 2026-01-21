const {__} = wp.i18n;

export const PostParentLink = ({parent}) => {
  return <div className="components-panel__body is-opened">
    <p>{ __('This is a sub-page of', 'planet4-master-theme-backend') }</p>
    <a
      href={window.location.href.replace(/\?post=\d+/, `?post=${parent.id}`)}>
      { parent.title.raw }
    </a>
    <p>{ __('Style and analytics settings from the parent page will be used.', 'planet4-master-theme-backend') }</p>
  </div>;
};
