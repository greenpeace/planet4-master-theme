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
const {apiFetch} = wp;
const {addQueryArgs} = wp.url;
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

const initializeInstagramEmbeds = () => {
  setTimeout(() => {
    if ('undefined' !== window.instgrm) {
      window.instgrm.Embeds.process();
    }
  }, 3000);
};

const initializeFacebookEmbeds = () => {
  setTimeout(() => {
    if ('undefined' !== window.FB) {
      window.FB.XFBML.parse();
    }
  }, 3000);
};

const PROVIDER_SCRIPT_DATA = {
  facebook: {
    script: 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v9.0',
    initFunction: initializeFacebookEmbeds,
  },
  instagram: {
    script: 'https://www.instagram.com/embed.js',
    initFunction: initializeInstagramEmbeds,
  },
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
    animation,
  } = attributes;

  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value,
  });

  /**
   * Check if social media corresponding embeds script is loaded and initiliaze it.
   * Can be used for Facebook and Instagram depending on the parameter.
   *
   * @param {Object} provider
   */
  const checkProviderScript = async provider => {
    const providerData = PROVIDER_SCRIPT_DATA[provider];
    const script = document.querySelector(`body > script[src="${providerData.script}"]`);
    if (script === null) {
      await loadScriptAsync(providerData.script);
    }
    providerData.initFunction();
  };

  const updateEmbed = async (url, provider) => {
    if (!url) {
      setAttributes({embed_code: ''});
      return;
    }

    let embedCode;
    try {
      if (provider === 'instagram') {
        const instagramEmbedData = await apiFetch({path: addQueryArgs('planet4/v1/get-instagram-embed', {url})});

        if (instagramEmbedData) {
          // WordPress automatically adds rel="noopener" to links that have _blank target.
          // The Instagram embed HTML doesn't, so in order to avoid block validation errors we need to add it ourselves.
          embedCode = instagramEmbedData.replaceAll('target="_blank"', 'target="_blank" rel="noopener noreferrer"');
        }
      }
    } catch (error) {
      embedCode = '';
    }
    setAttributes({embed_code: embedCode});
  };

  useEffect(() => {
    const provider = ALLOWED_OEMBED_PROVIDERS.find(allowedProvider => social_media_url.includes(allowedProvider));

    if (!provider) {
      setAttributes({embed_code: ''});
      return;
    }

    (async () => {
      await checkProviderScript(provider);

      // For Facebook we don't need the embed HTML code since we use an iframe
      if (provider !== 'facebook') {
        updateEmbed(social_media_url, provider);
      }
    })();
  }, [social_media_url]);

  const embed_type_help = __('Select oEmbed for the following types of social media<br>- Facebook: post, activity, photo, video, media, question, note<br>- Instagram: image', 'planet4-blocks-backend');

  const renderEditInPlace = () => (
    <>
      <header>
        <RichText
          tagName="h2"
          className="page-section-header"
          placeholder={__('Enter title', 'planet4-blocks-backend')}
          value={title}
          onChange={toAttribute('title')}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
      </header>
      <RichText
        tagName="p"
        className="page-section-description"
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        value={description}
        onChange={toAttribute('description')}
        withoutInteractiveFormatting
        allowedFormats={['core/bold', 'core/italic']}
      />
    </>
  );

  const renderSidebar = () => (
    <InspectorControls>
      <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
        <RadioControl
          label={__('Embed type', 'planet4-blocks-backend')}
          options={[
            {label: __('oEmbed', 'planet4-blocks-backend'), value: OEMBED_EMBED_TYPE},
            {label: __('Facebook page', 'planet4-blocks-backend'), value: FACEBOOK_EMBED_TYPE},
          ]}
          selected={embed_type}
          onChange={toAttribute('embed_type')}
        />
        <HTMLSidebarHelp>{embed_type_help}</HTMLSidebarHelp>
        {embed_type === FACEBOOK_EMBED_TYPE &&
          <>
            <label htmlFor="render-siderbar__control">
              {__('What Facebook page content would you like to display?', 'planet4-blocks-backend')}
            </label>
            <SelectControl
              id="render-siderbar__control"
              value={facebook_page_tab}
              options={[
                {label: __('Timeline', 'planet4-blocks-backend'), value: FACEBOOK_PAGE_TAB_TIMELINE},
                {label: __('Events', 'planet4-blocks-backend'), value: FACEBOOK_PAGE_TAB_EVENTS},
                {label: __('Messages', 'planet4-blocks-backend'), value: FACEBOOK_PAGE_TAB_MESSAGES},
              ]}
              onChange={toAttribute('facebook_page_tab')}
            />
          </>
        }
        <URLInput
          label={__('URL', 'planet4-blocks-backend')}
          placeholder={__('Enter URL', 'planet4-blocks-backend')}
          value={social_media_url}
          onChange={toAttribute('social_media_url')}
        />
        <SelectControl
          label={__('Alignment', 'planet4-blocks-backend')}
          value={alignment_class}
          options={[
            {label: __('None', 'planet4-blocks-backend'), value: ''},
            {label: __('Left', 'planet4-blocks-backend'), value: 'alignleft'},
            {label: __('Center', 'planet4-blocks-backend'), value: 'aligncenter'},
            {label: __('Right', 'planet4-blocks-backend'), value: 'alignright'},
          ]}
          onChange={toAttribute('alignment_class')}
        />
      </PanelBody>
      <PanelBody title={__('Animations ', 'planet4-blocks-backend')} initialOpen={true}>
        <SelectControl
          label={__('Select Animation', 'planet4-blocks-backend')}
          value={animation}
          options={[
            {label: __('None', 'planet4-blocks-backend'), value: ''},
            {label: __('Slide In Up', 'planet4-blocks-backend'), value: 'animate__slideInUp'},
            {label: __('Slide In Down', 'planet4-blocks-backend'), value: 'animate__slideInDown'},
            {label: __('Slide In Left', 'planet4-blocks-backend'), value: 'animate__slideInLeft'},
            {label: __('Slide In Right', 'planet4-blocks-backend'), value: 'animate__slideInRight'},
          ]}
          onChange={toAttribute('animation')}
        />
      </PanelBody>
      <PanelBody title={__('Learn more about this block', 'planet4-blocks-backend')} initialOpen={false}>
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
        embedCode={embed_code || ''}
        facebookPageTab={facebook_page_tab}
        facebookPageUrl={social_media_url}
        alignmentClass={alignment_class}
        embedType={embed_type}
      />
    </section>
  );
};
