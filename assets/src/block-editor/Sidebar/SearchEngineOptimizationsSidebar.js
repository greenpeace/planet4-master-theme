import {URLInput} from '../URLInput/URLInput';

const {PluginDocumentSettingPanel} = wp.editor;
const {useDispatch, useSelect} = wp.data;
const {__, sprintf} = wp.i18n;
const {TextControl, TextareaControl, ExternalLink} = wp.components;
const {createInterpolateElement, useMemo} = wp.element;

const CANONICAL_URL = 'p4_seo_canonical_url';
const META_TITLE = 'p4_seo_meta_title';
const META_DESCRIPTION = 'p4_seo_meta_description';
const OG_TITLE = 'p4_og_title';
const OG_DESCRIPTION = 'p4_og_description';

const TITLE_MAX = 60;
const DESCRIPTION_MAX = 120;

const truncate = (text, max) => {
  if (text.length <= max) {
    return text;
  }
  return `${text.slice(0, max).trimEnd()}…`;
};

export const SearchEngineOptimizationsSidebar = {
  getId: () => 'planet4-seo-sidebar',
  render: () => {
    const {editPost} = useDispatch('core/editor');

    const {meta, postTitle, excerpt, permalink, siteTitle} = useSelect(select => {
      const editor = select('core/editor');
      const core = select('core');
      const site = core.getEntityRecord('root', 'site');

      return {
        meta: editor.getEditedPostAttribute('meta')|| '',
        postTitle: editor.getEditedPostAttribute('title') || site?.title || '',
        excerpt: editor.getEditedPostAttribute('excerpt') || site?.description || '',
        permalink: editor.getPermalink() || '',
        siteTitle: site?.title || __('Greenpeace', 'planet4-master-theme-backend'),
      };
    }, []);

    const url = meta[CANONICAL_URL] || permalink || window.location.origin;
    const title = meta[META_TITLE] || meta[OG_TITLE] || postTitle || '';
    const description = meta[META_DESCRIPTION] || meta[OG_DESCRIPTION] || excerpt || '';
    const titleLength = meta[META_TITLE].length;
    const descriptionLength = meta[META_DESCRIPTION].length;

    const previewTitle = truncate(title, TITLE_MAX);
    const previewDescription = truncate(description, DESCRIPTION_MAX);

    const breadcrumb = useMemo(() => {
      try {
        const {hostname, pathname} = new URL(url);
        const parts = pathname.split('/').filter(Boolean);
        return [hostname, ...parts].join(' › ');
      } catch {
        return url;
      }
    }, [url]);

    return (
      <PluginDocumentSettingPanel
        name="planet4-search-engine-optimizations"
        title={__('Search Engine Optimizations', 'planet4-master-theme-backend')}
      >
        <div className="planet4-seo-sidebar">
          <p className="components-base-control__help css-p914ie-StyledHelp">
            {__('Draft your meta title and description below to see a real-tme preview of how your page appears in mobile search results.', 'planet4-master-theme-backend')}
          </p>
          <p className="components-base-control__help serp-help">
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
            help={<>
              <div className="serp-help__textcontrol">
                <div>&#10003; {__('Suggested: 50-60 characters.', 'planet4-master-theme-backend')}</div>
                <div className={titleLength > TITLE_MAX ? 'serp-help__textcontrol-error' : ''}>{
                  sprintf(
                    /* translators: %d: number of characters */
                    __('%d characters', 'planet4-master-theme-backend'), titleLength
                  )}
                </div>
              </div>
              <div>
                {__('Add a unique, descriptive page title. Leave empty to use the Open Graph title (if set) or page title.', 'planet4-master-theme-backend')}
              </div>
            </>}
          />
          <TextareaControl
            label={__('Meta Description', 'planet4-master-theme-backend')}
            value={meta[META_DESCRIPTION] || ''}
            onChange={value => editPost({meta: {[META_DESCRIPTION]: value}})}
            help={<>
              <div className="serp-help__textcontrol">
                <div>&#10003; {__('Suggested: 120-160 characters.', 'planet4-master-theme-backend')}</div>
                <div className={descriptionLength > DESCRIPTION_MAX ? 'serp-help__textcontrol-error' : ''}>{
                  sprintf(
                    /* translators: %d: number of characters */
                    __('%d characters', 'planet4-master-theme-backend'), descriptionLength
                  )}
                </div>
              </div>
              <div>
                {__('Encourage clicks. Leave empty to use Open Graph description or post excerpt.', 'planet4-master-theme-backend')}
              </div>
            </>}
          />
          <div className="serp-preview-head">
            {__('Mobile search preview', 'planet4-master-theme-backend')}
          </div>
          <div className="serp-preview" aria-label={__('Mobile search result preview', 'planet4-master-theme-backend')}>
            <div className="serp-preview__card">
              <div className="serp-preview__site">
                <span className="serp-preview__favicon" aria-hidden="true"></span>
                <div className="serp-preview__site-text">
                  <div className="serp-preview__site-name">
                    {siteTitle}
                  </div>
                  <div className="serp-preview__breadcrumb">
                    {breadcrumb}
                  </div>
                </div>
                <div className="serp-preview_dots"></div>
              </div>
              <div className={titleLength === 0 ? 'serp-preview__title fallback' : 'serp-preview__title'}>
                {previewTitle}
              </div>
              <div className={descriptionLength === 0 ? 'serp-preview__description fallback' : 'serp-preview__description'}>
                {previewDescription}
              </div>
            </div>
          </div>
          <p className="components-base-control__help css-p914ie-StyledHelp">
            {__('Estimated preview only. Search Engines may rewrite titles or descriptions based on the user\'s search', 'planet4-master-theme-backend')}
          </p>
          <URLInput
            label={__('Canonical link', 'planet4-master-theme-backend')}
            value={meta[CANONICAL_URL]}
            onChange={value => editPost({meta: {[CANONICAL_URL]: value}})}
            help={__('Leave empty to use this page\'s URL', 'planet4-master-theme-backend')}
          />
        </div>
      </PluginDocumentSettingPanel>
    );
  },
};
