import { LAYOUT_NO_IMAGE, LAYOUT_ICONS, LAYOUT_TASKS, LAYOUT_IMAGES, MAX_COLUMNS_AMOUNT } from './ColumnConstants';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import {
  Tooltip,
  Button,
} from '@wordpress/components';

const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;

export const EditableColumns = ({
  columns_block_style,
  toAttribute,
  columns,
  isCampaign,
  column_img,
  addColumn,
  removeColumn
}) => {
  const getImageOrButton = (openEvent, index) => {
    if (columns[index] && columns[index].attachment && (0 < columns[index].attachment)) {
      return (
        <>
          <div className='img-wrap'>
            <div className='buttons-overlay'>
              <Button
                onClick={openEvent}
                icon='edit'
              >
                {__('Edit image', 'planet4-blocks-backend')}
              </Button>
              <Button
                onClick={() => toAttribute('attachment', index)(0)}
                icon='trash'
              >
                {__('Remove image', 'planet4-blocks-backend')}
              </Button>
            </div>
            <img
              src={column_img[columns[index].attachment]}
              onClick={openEvent}
            />
          </div>
        </>

      );
    }

    return (
      <div className='column-img-btn-container'>
        <Button
          onClick={openEvent}
          isSecondary
        >
          {__('Select column image', 'planet4-blocks-backend')}
        </Button>
      </div>
    );
  };

  return (
    <section className={`block columns-block block-style-${columns_block_style}`}>
      <div className='container'>
        <div className={columns_block_style === LAYOUT_TASKS ? 'tasks-wrap can-do-steps d-lg-block' : ''}>
          <div className='row'>
            {columns.map((column, index) => (
              <div key={`column-${index}`} className={`col-md-6 col-lg column-wrap ${columns_block_style === LAYOUT_TASKS ? 'step-info' : ''}`}>
                {
                  <Tooltip position='top center' text={__('Remove column %s', 'planet4-blocks-backend').replace('%s', index + 1)}>
                    <Button
                      className='remove-column'
                      onClick={() => removeColumn(index)}
                      icon='trash'
                    />
                  </Tooltip>
                }
                {columns_block_style === LAYOUT_TASKS && (
                  <span className='step-number'>
                    <span className='step-number-inner'>{index + 1}</span>
                  </span>
                )}
                {[LAYOUT_ICONS, LAYOUT_IMAGES].includes(columns_block_style) && (
                  <div className='attachment-container'>
                    <MediaUploadCheck>
                      <MediaUpload
                        type='image'
                        onSelect={({ id }) => toAttribute('attachment', index)(id)}
                        value={column.attachment}
                        allowedTypes={columns_block_style === LAYOUT_ICONS ? ['image/png'] : ['image']}
                        render={({ open }) => getImageOrButton(open, index)}
                      />
                    </MediaUploadCheck>
                    {columns_block_style === LAYOUT_ICONS && column.attachment > 0 && typeof column_img[column.attachment] !== 'undefined' && !column_img[column.attachment].endsWith('.png') &&
                      <div className='column-image-error'>
                        {__('Please select another image for this column, as the current image is not an icon and you have chosen columns style icons. ', 'planet4-blocks-backend')}
                      </div>
                    }
                  </div>
                )}
                <RichText
                  tagName={columns_block_style === LAYOUT_TASKS ? 'h5' : 'h3'}
                  placeholder={__('Enter column header', 'planet4-blocks-backend')}
                  value={column.title}
                  onChange={toAttribute('title', index)}
                  keepPlaceholderOnFocus={true}
                  withoutInteractiveFormatting
                  allowedFormats={[]}
                  characterLimit={40}
                  multiline='false'
                />
                <RichText
                  tagName='p'
                  placeholder={__('Enter column description', 'planet4-blocks-backend')}
                  value={column.description}
                  onChange={toAttribute('description', index)}
                  keepPlaceholderOnFocus={true}
                  withoutInteractiveFormatting
                  characterLimit={400}
                  allowedFormats={[]}
                />
                {columns_block_style === LAYOUT_TASKS && (
                  <MediaUploadCheck>
                    <MediaUpload
                      type='image'
                      onSelect={({ id }) => toAttribute('attachment', index)(id)}
                      value={column.attachment}
                      allowedTypes={['image']}
                      render={({ open }) => getImageOrButton(open, index)}
                    />
                  </MediaUploadCheck>
                )}
                <RichText
                  tagName='div'
                  className={isCampaign || [LAYOUT_NO_IMAGE, LAYOUT_TASKS].includes(columns_block_style) ?
                    `btn btn-${isCampaign ? 'primary' : 'secondary'}` :
                    'call-to-action-link'
                  }
                  placeholder={[LAYOUT_NO_IMAGE, LAYOUT_TASKS].includes(columns_block_style) ?
                    __('Enter column button text', 'planet4-blocks-backend') :
                    __('Enter column link text', 'planet4-blocks-backend')
                  }
                  value={column.cta_text}
                  onChange={toAttribute('cta_text', index)}
                  keepPlaceholderOnFocus={true}
                  withoutInteractiveFormatting
                  allowedFormats={[]}
                />
              </div>
            ))}
            {columns.length < MAX_COLUMNS_AMOUNT && (
              <div className='col-md-6 col-lg column-wrap column-placeholder'>
                <div className='add-column' onClick={addColumn}>
                  <div>+</div>
                  {__('Add column', 'planet4-blocks-backend')}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
