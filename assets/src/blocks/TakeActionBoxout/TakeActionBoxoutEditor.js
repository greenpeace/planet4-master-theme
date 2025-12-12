import {URLInput} from '../../block-editor/URLInput/URLInput';
import {ImageHoverControls} from '../../block-editor/ImageHoverControls';
import {TakeActionBoxoutFrontend} from './TakeActionBoxoutFrontend';
import {ImagePlaceholder} from './ImagePlaceholder';
import {FONT_SIZES} from './HeadingFontSizes';
import {FontSizePicker} from '@wordpress/components';

const {useSelect} = wp.data;
const {RichText, BlockControls, MediaUpload, MediaUploadCheck, InspectorControls} = wp.blockEditor;
const {
  SelectControl,
  PanelBody,
  CheckboxControl,
  ToolbarGroup,
  ToolbarButton,
  Button,
  ToggleControl,
} = wp.components;
const {__} = wp.i18n;

// Planet 4 settings (Planet 4 >> Defaults content >> Actions default button text).
const DEFAULT_BUTTON_TEXT = window.p4_vars.options.take_action_covers_button_text || __('Take action', 'planet4-master-theme');

export const TakeActionBoxoutEditor = ({
  attributes,
  isSelected,
  setAttributes,
}) => {
  const {
    take_action_page,
    title: customTitle,
    excerpt: customExcerpt,
    link: customLink,
    linkText: customLinkText,
    newTab,
    imageId: customImageId,
    className,
    stickyOnMobile,
    headingFontSize,
  } = attributes;

  const {options: p4_options} = window.p4_vars;
  const isNewIA = p4_options.new_ia;

  const {
    loading,
    actPageList,
    title,
    excerpt,
    link,
    linkText,
    imageId,
    imageUrl,
    imageAlt,
  } = useSelect(select => {
    const postId = select('core/editor').getCurrentPostId();
    const args = {
      per_page: -1,
      sort_order: 'asc',
      sort_column: 'post_title',
      post_status: 'publish',
    };

    // eslint-disable-next-line no-shadow
    const actPageList = [].concat(
      select('core').getEntityRecords('postType', 'page', {
        ...args,
        parent: isNewIA ? p4_options.take_action_page : p4_options.act_page,
      }) || [],
      ...(isNewIA ? (select('core').getEntityRecords('postType', 'p4_action', args) || []) : [])
    ).filter(a => parseInt(a.id) !== postId).sort((a, b) => {
      if (a.title.raw === b.title.raw) {
        return 0;
      }
      return a.title.raw > b.title.raw ? 1 : -1;
    });
    const actPage = actPageList.find(actPageFound => take_action_page === actPageFound.id);

    // Because `useSelect` does an API call to fetch data, the actPageList will be empty the first time it's called.
    // Or first few times.
    if (take_action_page && !actPage) {
      return {loading: true};
    }
    const actPageImageId = actPage?.featured_media;

    const customImage = customImageId && select('core').getMedia(customImageId);
    const customImageFromId = customImage?.source_url;

    const title = !take_action_page ? customTitle : actPage.title.raw; // eslint-disable-line no-shadow
    const excerpt = !take_action_page ? customExcerpt : actPage.excerpt.raw; // eslint-disable-line no-shadow
    const link = !take_action_page ? customLink : actPage.link; // eslint-disable-line no-shadow

    const linkText = !take_action_page ? customLinkText : actPage?.meta?.action_button_text || DEFAULT_BUTTON_TEXT; // eslint-disable-line no-shadow

    const imageId = !take_action_page ? customImageId : actPageImageId; // eslint-disable-line no-shadow
    const imageUrl = !take_action_page ? customImageFromId : select('core').getMedia(actPageImageId)?.source_url; // eslint-disable-line no-shadow
    const imageAlt = !take_action_page ? customImage?.alt_text : ''; // eslint-disable-line no-shadow

    return {
      actPageList,
      title,
      excerpt,
      link,
      linkText,
      imageId,
      imageUrl,
      imageAlt,
    };
  }, [take_action_page, customTitle, customExcerpt, customLink, customLinkText, customImageId]);

  const takeActionPageSelected = take_action_page && parseInt(take_action_page) > 0;

  if (loading || !actPageList.length) {
    return __('Populating block\'s fields…', 'planet4-master-theme-backend');
  }

  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value,
  });

  const removeImage = () => setAttributes({imageId: null});

  const selectImage = ({id}) => setAttributes({imageId: id});

  const actPageOptions = actPageList.map(actPage => ({label: actPage.title.raw, value: actPage.id}));

  const postHasStickyBoxoutAlready = document.querySelector('#action-card');

  const renderEditInPlace = () => (takeActionPageSelected ?
    <TakeActionBoxoutFrontend {...attributes} {...{title, excerpt, link, linkText, imageUrl, imageAlt}} /> :
    <section
      className={`boxout ${className || ''}`}
      {...stickyOnMobile && {id: 'action-card'}}
    >
      <div className={'boxout-image-container'}>
        <MediaUploadCheck>
          <MediaUpload
            type="image"
            onSelect={selectImage}
            value={imageId}
            allowedTypes={['image']}
            render={({open}) => <ImageHoverControls
              onEdit={open}
              onRemove={removeImage}
              isAdd={!imageUrl}
            />}
          />
        </MediaUploadCheck>
        {!imageUrl ? <ImagePlaceholder /> : <img src={imageUrl} alt={imageAlt} />}
      </div>
      <div className="boxout-content">
        <RichText
          tagName="div"
          className={`boxout-heading ${headingFontSize}`}
          placeholder={__('Enter title', 'planet4-master-theme-backend')}
          value={title}
          onChange={toAttribute('title')}
          disabled={true}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
        <RichText
          tagName="p"
          className="boxout-excerpt"
          placeholder={__('Enter description', 'planet4-master-theme-backend')}
          value={excerpt}
          onChange={toAttribute('excerpt')}
          disabled={takeActionPageSelected}
          withoutInteractiveFormatting
          allowedFormats={['core/bold', 'core/italic']}
        />
        <RichText
          tagName="div"
          className="btn btn-primary"
          placeholder={__('Button text', 'planet4-master-theme-backend')}
          value={linkText}
          onChange={toAttribute('linkText')}
          disabled={takeActionPageSelected}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
      </div>
    </section>
  );

  const renderSidebar = () => (
    <>
      <InspectorControls>
        <PanelBody title={__('Styles', 'planet4-master-theme-backend')}>
          <div className="sticky-boxout-checkbox">
            <ToggleControl
              __nextHasNoMarginBottom
              label={__('Make block stick to the bottom of the page on mobile', 'planet4-master-theme-backend')}
              value={stickyOnMobile}
              checked={stickyOnMobile}
              onChange={toAttribute('stickyOnMobile')}
              disabled={!stickyOnMobile && postHasStickyBoxoutAlready}
              help={!stickyOnMobile && postHasStickyBoxoutAlready ? __('You can only have one sticky boxout per post', 'planet4-master-theme-backend') : ''}
            />
          </div>
        </PanelBody>
        <PanelBody title={__('Settings', 'planet4-master-theme-backend')}>
          <SelectControl
            __nextHasNoMarginBottom
            __next40pxDefaultSize
            label={__('Select Take Action Page:', 'planet4-master-theme-backend')}
            value={take_action_page}
            options={[
              {label: __('None (custom)', 'planet4-master-theme-backend'), value: 0},
              ...actPageOptions,
            ]}
            onChange={page => setAttributes({take_action_page: parseInt(page)})}
          />
          <FontSizePicker
            __next40pxDefaultSize
            fontSizes={FONT_SIZES}
            value={headingFontSize}
            onChange={fontSize => setAttributes({headingFontSize: fontSize})}
            disableCustomFontSizes
          />
          {!takeActionPageSelected && <URLInput
            label={__('Custom link', 'planet4-master-theme-backend')}
            placeholder={__('Enter custom link', 'planet4-master-theme-backend')}
            value={link}
            onChange={value => {
              if (!take_action_page) {
                setAttributes({link: value});
              }
            }}
          />}
          {!takeActionPageSelected &&
            <CheckboxControl
              __nextHasNoMarginBottom
              label={__('Open in a new tab', 'planet4-master-theme-backend')}
              value={newTab}
              checked={newTab}
              onChange={toAttribute('newTab')}
              disabled={takeActionPageSelected}
            />
          }
          {!takeActionPageSelected &&
            <MediaUploadCheck>
              <MediaUpload
                title={__('Select Background Image', 'planet4-master-theme-backend')}
                type="image"
                onSelect={selectImage}
                value={imageId}
                allowedTypes={['image']}
                render={({open}) => (
                  <Button
                    onClick={open}
                    className="button"
                    disabled={takeActionPageSelected}
                  >
                    + { imageId ? __('Change Background Image', 'planet4-master-theme-backend') : __('Select Background Image', 'planet4-master-theme-backend') }
                  </Button>
                )}
              />
            </MediaUploadCheck>
          }
        </PanelBody>
        <PanelBody title={__('Learn more about this block', 'planet4-master-theme-backend')} initialOpen={false}>
          <p className="components-base-control__help">
            <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/take-action-boxout/" rel="noreferrer">
            P4 Handbook Take Action Boxout
            </a>
            {' '} &#128499;&#65039;;
          </p>
        </PanelBody>
      </InspectorControls>
      {!takeActionPageSelected && imageId &&
        <BlockControls>
          <ToolbarGroup>
            <MediaUploadCheck>
              <MediaUpload
                onSelect={selectImage}
                allowedTypes={['image']}
                value={imageId}
                type="image"
                render={({open}) => (
                  <ToolbarButton
                    className="components-icon-button components-toolbar__control"
                    label={__('Edit Image', 'planet4-master-theme-backend')}
                    onClick={open}
                    icon="edit"
                  />
                )}
              />
            </MediaUploadCheck>
            <ToolbarButton
              className="components-icon-button components-toolbar__control"
              label={__('Remove Image', 'planet4-master-theme-backend')}
              onClick={removeImage}
              icon="trash"
            />
          </ToolbarGroup>
        </BlockControls>
      }
    </>
  );

  return (
    <>
      {isSelected && renderSidebar()}
      {renderEditInPlace()}
    </>
  );
};
