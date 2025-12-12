import {URLInput} from '../URLInput/URLInput';

const {PluginDocumentSettingPanel} = wp.editor;
const {useDispatch, useSelect} = wp.data;
const {__} = wp.i18n;

const CANONICAL_URL = 'p4_seo_canonical_url';

export const SearchEngineOptimizationsSidebar = {
  getId: () => 'planet4-seo-sidebar',
  render: () => {
    const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta'), []);
    const {editPost} = useDispatch('core/editor', [meta[CANONICAL_URL]]);

    return (
      <PluginDocumentSettingPanel
        name="planet4-search-engine-optimizations"
        title={__('Search Engine Optimizations', 'planet4-master-theme-backend')}
      >
        <URLInput
          label={__('Canonical link', 'planet4-master-theme-backend')}
          value={meta[CANONICAL_URL]}
          onChange={value => editPost({meta: {[CANONICAL_URL]: value}})}
          help={__('If emtpy a self-reference canonical link will be used', 'planet4-master-theme-backend')}
        />
      </PluginDocumentSettingPanel>
    );
  },
};
