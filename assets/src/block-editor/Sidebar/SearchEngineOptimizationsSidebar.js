import {URLInput} from '../URLInput/URLInput';

const {PluginDocumentSettingPanel} = wp.editor;
const {useDispatch, useSelect} = wp.data;
const {__} = wp.i18n;
const {TextControl, TextareaControl, ExternalLink} = wp.components;
const {createInterpolateElement} = wp.element;

const CANONICAL_URL = 'p4_seo_canonical_url';
const META_TITLE = 'p4_seo_meta_title';
const META_DESCRIPTION = 'p4_seo_meta_description';

export const SearchEngineOptimizationsSidebar = {
  getId: () => 'planet4-seo-sidebar',
  render: () => {
    const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta'), []);
    const {editPost} = useDispatch('core/editor');

    return (
      <PluginDocumentSettingPanel
        name="planet4-search-engine-optimizations"
        title={__('Search Engine Optimizations', 'planet4-master-theme-backend')}
      >
        <div className="planet4-seo-sidebar">
          <p className="components-base-control__help">
            {createInterpolateElement(
              __('Check optimal length at <a>SERP Snippet Generator</a>.', 'planet4-master-theme-backend'),
              {
                a: (
                  <ExternalLink href="https://app.sistrix.com/en/serp-snippet-generator" />
                ),
              }
            )}
          </p>
          <TextControl
            label={__('Meta Title', 'planet4-master-theme-backend')}
            value={meta[META_TITLE] || ''}
            onChange={value => editPost({meta: {[META_TITLE]: value}})}
            help={__('Leave empty to use the Open Graph Title (if set) or the Post Title.', 'planet4-master-theme-backend')}
          />
          <TextareaControl
            label={__('Meta Description', 'planet4-master-theme-backend')}
            value={meta[META_DESCRIPTION] || ''}
            onChange={value => editPost({meta: {[META_DESCRIPTION]: value}})}
            help={__('Leave empty to use Open Graph Description (if set) or the Post Excerpt.', 'planet4-master-theme-backend')}
          />
          <URLInput
            label={__('Canonical link', 'planet4-master-theme-backend')}
            value={meta[CANONICAL_URL]}
            onChange={value => editPost({meta: {[CANONICAL_URL]: value}})}
            help={__('If emtpy a self-reference canonical link will be used', 'planet4-master-theme-backend')}
          />
        </div>
      </PluginDocumentSettingPanel>
    );
  },
};
