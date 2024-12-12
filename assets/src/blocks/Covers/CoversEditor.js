import {useEffect} from '@wordpress/element';

import {InspectorControls, RichText} from '@wordpress/block-editor';
import TagSelector from '../../block-editor/TagSelector';
import {PostSelector} from '../../block-editor/PostSelector';
import PostTypeSelector from '../../block-editor/PostTypeSelector';
import {Covers} from './Covers';
import {COVERS_TYPES, COVERS_LAYOUTS, CAROUSEL_LAYOUT_COVERS_LIMIT} from './CoversConstants';
import {useCovers} from './useCovers';
import {getStyleFromClassName} from '../../functions/getStyleFromClassName';
import {CoversCarouselLayout} from './CoversCarouselLayout';

const {
  SelectControl,
  PanelBody,
  RadioControl,
  TextControl,
  Tooltip,
} = wp.components;
const {__} = wp.i18n;

const renderEdit = (attributes, toAttribute, setAttributes) => {
  const {initialRowsLimit, posts, tags, cover_type, post_types, layout, readMoreText} = attributes;

  const rowLimitOptions = [
    {label: __('1 Row', 'planet4-blocks-backend'), value: 1},
    {label: __('2 Rows', 'planet4-blocks-backend'), value: 2},
    {label: __('All rows', 'planet4-blocks-backend'), value: 0},
  ];

  return (
    <InspectorControls>
      <PanelBody title={__('Layout', 'planet4-blocks-backend')}>
        <RadioControl
          options={[
            {label: __('Carousel', 'planet4-blocks-backend'), value: COVERS_LAYOUTS.carousel},
            {label: __('Grid', 'planet4-blocks-backend'), value: COVERS_LAYOUTS.grid},
          ]}
          selected={layout}
          onChange={value => {
            setAttributes({
              layout: value,
              initialRowsLimit: value === COVERS_LAYOUTS.carousel ? 1 : initialRowsLimit,
            });
          }}
        />
      </PanelBody>
      <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
        {layout !== COVERS_LAYOUTS.carousel &&
          <SelectControl
            label="Rows to display"
            value={initialRowsLimit}
            options={rowLimitOptions}
            onChange={value => toAttribute('initialRowsLimit')(Number(value))}
          />
        }

        {!posts.length &&
          <div>
            <TagSelector
              value={tags}
              onChange={toAttribute('tags')}
              maxLength={null}
            />
            <p className="FieldHelp">
              {__('Associate this block with Actions that have specific Tags', 'planet4-blocks-backend')}
            </p>
          </div>
        }

        {cover_type === COVERS_TYPES.content && !posts.length &&
          <PostTypeSelector
            value={post_types}
            onChange={toAttribute('post_types')}
          />
        }
        {!tags.length && !post_types.length &&
          <div>
            <label htmlFor="post-selector__control">{__('Manual override', 'planet4-blocks-backend')}</label>
            <PostSelector
              id="post-selector__control"
              label={__('Select pages', 'planet4-blocks-backend')}
              selected={posts || []}
              onChange={toAttribute('posts')}
              postType={cover_type === COVERS_TYPES.content ? 'post,page' : 'act_page'}
              placeholder={__('Select pages', 'planet4-blocks-backend')}
              maxLength={layout === COVERS_LAYOUTS.carousel ? CAROUSEL_LAYOUT_COVERS_LIMIT : null}
            />
          </div>
        }
        {layout !== COVERS_LAYOUTS.carousel &&
          <TextControl
            label={__('Button Text', 'planet4-blocks-backend')}
            placeholder={__('Override button text', 'planet4-blocks-backend')}
            help={__('Your default is set to [ Load more ]', 'planet4-blocks-backend')}
            value={readMoreText}
            onChange={toAttribute('readMoreText')}
          />
        }
      </PanelBody>
    </InspectorControls>
  );
};

const renderView = (attributes, toAttribute) => {
  const {
    initialRowsLimit,
    cover_type,
    title,
    description,
    className,
    layout,
    isExample,
    exampleCovers,
    readMoreText,
  } = attributes;
  const {covers, loading, row, amountOfCoversPerRow, isSmallWindow} = useCovers(attributes);

  const isCarouselLayout = layout === COVERS_LAYOUTS.carousel;

  const coversProps = {
    covers: isExample ? exampleCovers[cover_type] : covers,
    initialRowsLimit,
    row,
    showMoreCovers: () => {},
    cover_type,
    inEditor: true,
    isCarouselLayout,
    amountOfCoversPerRow,
    isExample,
    readMoreText,
  };

  const showLoadMoreButton = !isCarouselLayout && !!initialRowsLimit && covers.length > (amountOfCoversPerRow * row);

  return (
    <section className={`block covers-block ${cover_type}-covers-block ${className ?? ''} ${layout}-layout`}>
      {!isExample &&
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
      }
      {!loading && !covers.length && !isExample ?
        <div className="EmptyMessage">
          {__('Block content is empty. Check the block\'s settings or remove it.', 'planet4-blocks-backend')}
        </div> :
        <>
          {isCarouselLayout && !isSmallWindow ? <CoversCarouselLayout {...coversProps} /> : <Covers {...coversProps} />}
          {showLoadMoreButton && (
            <Tooltip text={__('Edit text', 'planet4-blocks-backend')}>
              <button className="btn btn-secondary load-more-btn">
                <RichText
                  tagName="div"
                  placeholder={__('Enter text', 'planet4-blocks-backend')}
                  value={readMoreText}
                  onChange={toAttribute('readMoreText')}
                  withoutInteractiveFormatting
                  allowedFormats={[]}
                />
              </button>
            </Tooltip>
          )
          }
        </>
      }
    </section>
  );
};

export const CoversEditor = ({attributes, setAttributes, isSelected}) => {
  const {className} = attributes;

  useEffect(() => {
    const styleClass = getStyleFromClassName(className);
    if (styleClass) {
      setAttributes({
        cover_type: styleClass,
      });
    }
  }, [className]);

  const toAttribute = attributeName => value => setAttributes({[attributeName]: value});

  return (
    <>
      {isSelected && renderEdit(attributes, toAttribute, setAttributes)}
      {renderView(attributes, toAttribute)}
    </>
  );
};

