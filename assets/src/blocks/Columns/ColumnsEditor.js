import {
  ToggleControl,
  PanelBody,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';

import { URLInput } from '../../components/URLInput/URLInput';
import { EditableColumns } from './EditableColumns';

const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;

const renderEdit = (attributes, setAttributes) => {

  const onCTALinkChange = (index, value) => {
    let columns = JSON.parse(JSON.stringify(attributes.columns));
    columns[index].cta_link = value;
    setAttributes({ columns });
  }

  const onLinkNewTabChange = (index, value) => {
    let columns = JSON.parse(JSON.stringify(attributes.columns));
    columns[index].link_new_tab = value;
    setAttributes({ columns });
  }

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
          {attributes.columns.map((column, index) => (
            <div key={`column-${index}`}>
              <p>{__('Column %s', 'planet4-blocks-backend').replace('%s', index + 1)}</p>
              <URLInput
                label={__('Button link', 'planet4-blocks-backend')}
                placeholder={__('Enter link for column %s', 'planet4-blocks-backend').replace('%s', index + 1)}
                value={column.cta_link}
                onChange={value => onCTALinkChange(index, value)}
              />
              <ToggleControl
                label={__('Open link in new tab', 'planet4-blocks-backend')}
                value={column.link_new_tab}
                checked={column.link_new_tab}
                onChange={value => onLinkNewTabChange(index, value)}
              />
            </div>
          ))}
        </PanelBody>
      </InspectorControls>
    </>
  );
};

const renderView = (attributes, setAttributes, column_img) => {
  const { columns_description, columns_title, columns_block_style } = attributes;

  const toAttribute = (attributeName, index) => value => {
    if (['columns_title', 'columns_description'].includes(attributeName)) {
      setAttributes({
        [attributeName]: value
      });
    } else {
      let columns = JSON.parse(JSON.stringify(attributes.columns));
      columns[index][attributeName] = value;
      setAttributes({ columns });
    }
  }

  const { postType } = useSelect(select => ({
    postType: select('core/editor').getCurrentPostType()
  }), []);

  return (
    <>
      <header className='articles-title-container'>
        <RichText
          tagName='h2'
          className='page-section-header'
          placeholder={__('Enter title', 'planet4-blocks-backend')}
          value={columns_title}
          onChange={toAttribute('columns_title')}
          keepPlaceholderOnFocus={true}
          withoutInteractiveFormatting
          allowedFormats={[]}
          characterLimit={40}
          multiline='false'
        />
      </header>
      <RichText
        tagName='p'
        className='page-section-description'
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        value={columns_description}
        onChange={toAttribute('columns_description')}
        keepPlaceholderOnFocus={true}
        withoutInteractiveFormatting
        characterLimit={200}
        allowedFormats={[]}
      />
      <EditableColumns
        isCampaign={postType === 'campaign'}
        columns={attributes.columns}
        columns_block_style={columns_block_style}
        toAttribute={toAttribute}
        column_img={column_img}
        addColumn={() =>
          setAttributes({
            columns: [
              ...attributes.columns,
              {
                title: '',
                description: '',
                attachment: 0,
                cta_link: '',
                cta_text: '',
                link_new_tab: '',
              }
            ]
          })
        }
        removeColumn={index => setAttributes({ columns: attributes.columns.splice(index, 1) })}
      />
    </>
  );
}

export const ColumnsEditor = ({ isSelected, attributes, setAttributes }) => {
  const { columns, className } = attributes;

  const { column_img } = useSelect(select => {
    let column_img = [];
    columns.forEach(column => {
      if (column.attachment && column.attachment > 0) {
        const media_details = select('core').getMedia(column.attachment);
        if (media_details) {
          column_img[column.attachment] = select('core').getMedia(column.attachment).source_url;
        }
      }
    });
    return { column_img };
  }, [ columns ]);

  useEffect(() => {
    if (className && className.includes('is-style-')) {
      const newColumnsStyle = className.split('is-style-')[1];
      setAttributes({
        columns_block_style: newColumnsStyle
      });
    }
  }, [className]);

  return (
    <div>
      {isSelected && renderEdit(attributes, setAttributes)}
      {renderView(attributes, setAttributes, column_img)}
    </div>
  );
}
