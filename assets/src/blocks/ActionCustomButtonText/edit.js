const {useEntityProp} = wp.data;
const {__} = wp.i18n;

const DEFAULT_TEXT = window.p4_vars.options.take_action_covers_button_text || __('Take action', 'planet4-blocks');

export default function Edit({context: {postId}}) {
  const [metaFields] = useEntityProp('postType', 'p4_action', 'meta', postId);

  return (
    <button className="btn btn-primary btn-small">
      {metaFields?.action_button_text || DEFAULT_TEXT}
    </button>
  );
}
