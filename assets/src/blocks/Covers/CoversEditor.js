import {
  SelectControl,
  PanelBody
} from '@wordpress/components';
import { useEffect } from '@wordpress/element';

import { InspectorControls } from '@wordpress/block-editor';
import TagSelector from '../../components/TagSelector/TagSelector';
import PostSelector from '../../components/PostSelector/PostSelector';
import PostTypeSelector from '../../components/PostTypeSelector/PostTypeSelector';
import { Covers, COVER_TYPES } from './Covers';
import { getCoversClassName } from './getCoversClassName';
import { useCovers } from './useCovers';

const { RichText } = wp.blockEditor;
const { __ } = wp.i18n;

const renderEdit = (attributes, toAttribute) => {
  const { covers_view, posts, tags, cover_type, post_types } = attributes;

  const rowOptions = [
    { label: __('1 Row', 'planet4-blocks-backend'), value: '1' },
    { label: __('2 Rows', 'planet4-blocks-backend'), value: '2' },
    { label: __('All rows', 'planet4-blocks-backend'), value: '3' },
  ];

  return (
    <InspectorControls>
      <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
        <SelectControl
          label='Rows to display'
          value={covers_view}
          options={rowOptions}
          onChange={toAttribute('covers_view')}
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
          <div>
            <PostTypeSelector
              value={post_types}
              onChange={toAttribute('post_types')}
            />
          </div>
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
  const { covers_view, cover_type, title, description } = attributes;
  const blockClassName = getCoversClassName(cover_type, covers_view);

  const { covers, loading, row } = useCovers(attributes);

  const coversProps = {
    covers,
    covers_view,
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
    if (className) {
      let newCoverType = COVER_TYPES.content;
      if (className.includes('campaign')) {
        newCoverType = COVER_TYPES.campaign;
      } else if (className.includes('take-action')) {
        newCoverType = COVER_TYPES.takeAction;
      }
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

