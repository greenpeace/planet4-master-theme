import {SocialMediaEmbed} from './SocialMediaEmbed';
import {URLInput} from '../../block-editor/URLInput/URLInput';
import {HTMLSidebarHelp} from '../../block-editor/HTMLSidebarHelp/HTMLSidebarHelp';
import {
  OEMBED_EMBED_TYPE,
  FACEBOOK_EMBED_TYPE,
  FACEBOOK_PAGE_TAB_TIMELINE,
  FACEBOOK_PAGE_TAB_EVENTS,
  FACEBOOK_PAGE_TAB_MESSAGES,
  ALLOWED_OEMBED_PROVIDERS,
} from './SocialMediaConstants.js';

const {InspectorControls, RichText} = wp.blockEditor;
const {RadioControl, SelectControl, PanelBody} = wp.components;
const {__} = wp.i18n;
const {useEffect} = wp.element;

const loadScriptAsync = uri => {
  // eslint-disable-next-line no-unused-vars
  return new Promise((resolve, reject) => {
    const tag = document.createElement('script');
    tag.src = uri;
    tag.async = true;
    tag.onload = () => {
      resolve();
    };
    const body = document.getElementsByTagName('body')[0];
    body.appendChild(tag);
  });
};

export const SocialMediaEditor = ({
  attributes,
  isSelected,
  setAttributes,
}) => {
  const {
    social_media_url,
    facebook_page_tab,
    description,
    title,
    embed_type,
    alignment_class,
    embed_code,
    className,
  } = attributes;

  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value,
  });

  const extractFacebookPostParts = url => {
    try {
      const {pathname} = new URL(url);
      const [, pageId, postType, postId] = pathname.split('/');
      const facebookParts = {pageId, postType, postId};
      return facebookParts;
    } catch {
      return null;
    }
  };


  useEffect(() => {
    const provider = ALLOWED_OEMBED_PROVIDERS.find(
      allowedProvider => social_media_url.includes(allowedProvider)
    );

    if (!provider) {
      setAttributes({embed_code: ''});
      return;
    }

    if (provider === 'instagram') {
      loadScriptAsync('https://www.instagram.com/embed.js');
      setAttributes({embed_type: 'instagramPost'});
      return;
    }

    const facebookParts = extractFacebookPostParts(social_media_url);
    const isFacebookPage = !facebookParts?.postType || !facebookParts?.postId;
    setAttributes({
      embed_type: isFacebookPage ? 'facebookPage' : 'facebookPost',
      embed_code: social_media_url,
    });

  }, [social_media_url]);


  const embed_type_help = __('Select oEmbed for the following types of social media<br>- Facebook: post, activity, photo, video, media, question, note<br>- Instagram: image', 'planet4-master-theme-backend');

  const renderEditInPlace = () => (
    <>
      <header>
        <RichText
          tagName="h2"
          className="page-section-header"
          placeholder={__('Enter title', 'planet4-master-theme-backend')}
          value={title}
          onChange={toAttribute('title')}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
      </header>
      <RichText
        tagName="p"
        className="page-section-description"
        placeholder={__('Enter description', 'planet4-master-theme-backend')}
        value={description}
        onChange={toAttribute('description')}
        withoutInteractiveFormatting
        allowedFormats={['core/bold', 'core/italic']}
      />
    </>
  );

  const renderSidebar = () => (
    <InspectorControls>
      <PanelBody title={__('Settings', 'planet4-master-theme-backend')}>
        <RadioControl
          label={__('Embed type', 'planet4-master-theme-backend')}
          options={[
            {label: __('oEmbed', 'planet4-master-theme-backend'), value: OEMBED_EMBED_TYPE},
            {label: __('Facebook page', 'planet4-master-theme-backend'), value: FACEBOOK_EMBED_TYPE},
          ]}
          selected={embed_type}
          onChange={toAttribute('embed_type')}
        />
        <HTMLSidebarHelp>{embed_type_help}</HTMLSidebarHelp>
        {embed_type === FACEBOOK_EMBED_TYPE &&
          <>
            <label htmlFor="render-siderbar__control">
              {__('What Facebook page content would you like to display?', 'planet4-master-theme-backend')}
            </label>
            <SelectControl
              __nextHasNoMarginBottom
              __next40pxDefaultSize
              id="render-siderbar__control"
              value={facebook_page_tab}
              options={[
                {label: __('Timeline', 'planet4-master-theme-backend'), value: FACEBOOK_PAGE_TAB_TIMELINE},
                {label: __('Events', 'planet4-master-theme-backend'), value: FACEBOOK_PAGE_TAB_EVENTS},
                {label: __('Messages', 'planet4-master-theme-backend'), value: FACEBOOK_PAGE_TAB_MESSAGES},
              ]}
              onChange={toAttribute('facebook_page_tab')}
            />
          </>
        }
        <URLInput
          label={__('URL', 'planet4-master-theme-backend')}
          placeholder={__('Enter URL', 'planet4-master-theme-backend')}
          value={social_media_url}
          onChange={toAttribute('social_media_url')}
        />
        <SelectControl
          __nextHasNoMarginBottom
          __next40pxDefaultSize
          label={__('Alignment', 'planet4-master-theme-backend')}
          value={alignment_class}
          options={[
            {label: __('None', 'planet4-master-theme-backend'), value: ''},
            {label: __('Left', 'planet4-master-theme-backend'), value: 'alignleft'},
            {label: __('Center', 'planet4-master-theme-backend'), value: 'aligncenter'},
            {label: __('Right', 'planet4-master-theme-backend'), value: 'alignright'},
          ]}
          onChange={toAttribute('alignment_class')}
        />
      </PanelBody>
      <PanelBody title={__('Learn more about this block', 'planet4-master-theme-backend')} initialOpen={false}>
        <p className="components-base-control__help">
          <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/socials/" rel="noreferrer">
          P4 Handbook Meta Block
          </a>
          {' '} &#129331;
        </p>
      </PanelBody>
    </InspectorControls>
  );

  return (
    <section className={`block social-media-block ${className ?? ''}`}>
      {isSelected && renderSidebar()}
      {renderEditInPlace()}
      <SocialMediaEmbed
        embedCode={embed_code}
        facebookPageTab={facebook_page_tab}
        facebookPageUrl={social_media_url}
        alignmentClass={alignment_class}
        embedType={embed_type}
      />
    </section>
  );
};
