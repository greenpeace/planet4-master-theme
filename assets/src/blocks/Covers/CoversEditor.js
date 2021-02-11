import {
  SelectControl,
  PanelBody
} from '@wordpress/components';
import { useEffect } from '@wordpress/element';

import { InspectorControls } from '@wordpress/block-editor';
import TagSelector from '../../components/TagSelector/TagSelector';
import PostSelector from '../../components/PostSelector/PostSelector';
import PostTypeSelector from '../../components/PostTypeSelector/PostTypeSelector';
import { Covers, COVER_TYPES, getCoversClassName } from './Covers';
import { useCovers } from './useCovers';

const { RichText } = wp.blockEditor;
const { __ } = wp.i18n;

const renderEdit = (attributes, toAttribute) => {
  const { initialRowsLimit, posts, tags, cover_type, post_types } = attributes;

  const rowLimitOptions = [
    { label: __('1 Row', 'planet4-blocks-backend'), value: 1 },
    { label: __('2 Rows', 'planet4-blocks-backend'), value: 2 },
    { label: __('All rows', 'planet4-blocks-backend'), value: 0 },
  ];

  return (
    <InspectorControls>
      <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
        <SelectControl
          label='Rows to display'
          value={initialRowsLimit}
          options={rowLimitOptions}
          onChange={value => toAttribute('initialRowsLimit')(Number(value))}
        />

        {!posts.length &&
          <div>
            <TagSelector
              value={tags}
              onChange={toAttribute('tags')}
            />
            <p className='FieldHelp'>
              {__('Associate this block with Actions that have specific Tags', 'planet4-blocks-backend')}
            </p>
          </div>
        }

        {cover_type === COVER_TYPES.content && !posts.length &&
          <PostTypeSelector
            value={post_types}
            onChange={toAttribute('post_types')}
          />
        }

        {cover_type !== COVER_TYPES.campaign && !tags.length && !post_types.length &&
          <div>
            <label>{__('Manual override', 'planet4-blocks-backend')}</label>
            <PostSelector
              value={posts}
              onChange={toAttribute('posts')}
              placeholder={__('Select articles', 'planet4-blocks-backend')}
              postType={cover_type === COVER_TYPES.content ? 'post' : 'act_page'}
            />
          </div>
        }
      </PanelBody>
    </InspectorControls>
  );
}

const renderView = (attributes, toAttribute) => {
  const { initialRowsLimit, cover_type, title, description } = attributes;
  const blockClassName = getCoversClassName(cover_type);

  const { covers, loading, row } = useCovers(attributes);

  const coversProps = {
    covers,
    initialRowsLimit,
    row,
    loadMoreCovers: () => {},
    cover_type,
  };

  return (
    <section className={blockClassName}>
      <header>
        <RichText
          tagName='h2'
          className='page-section-header'
          placeholder={__('Enter title', 'planet4-blocks-backend')}
          value={title}
          onChange={toAttribute('title')}
          keepPlaceholderOnFocus={true}
          withoutInteractiveFormatting
          characterLimit={60}
          multiline='false'
          allowedFormats={[]}
        />
      </header>
      <RichText
        tagName='p'
        className='page-section-description'
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        value={description}
        onChange={toAttribute('description')}
        keepPlaceholderOnFocus={true}
        withoutInteractiveFormatting
        characterLimit={400}
        allowedFormats={[]}
      />
      {!loading && !covers.length ?
        <div className='EmptyMessage'>
          {__(`Block content is empty. Check the block's settings or remove it.`, 'planet4-blocks-backend')}
        </div> :
        <Covers {...coversProps} />
      }
    </section>
  );
}

export const CoversEditor = ({ attributes, setAttributes, isSelected }) => {
  const { className, post_types } = attributes;

  useEffect(() => {
    if (className && className.includes('is-style-')) {
      const newCoverType = className.split('is-style-')[1];
      setAttributes({
        cover_type: newCoverType,
        posts: [],
        post_types: className.includes('content') ? post_types : [],
      });
    }
  }, [className]);

  const toAttribute = attributeName => value => setAttributes({ [attributeName]: value });

  return (
    <>
      {isSelected && renderEdit(attributes, toAttribute)}
      {renderView(attributes, toAttribute)}
    </>
  );
}

