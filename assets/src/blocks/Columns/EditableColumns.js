import {LAYOUT_NO_IMAGE, LAYOUT_ICONS, LAYOUT_TASKS, LAYOUT_IMAGES} from './ColumnConstants';
import {ColumnsImagePlaceholder} from './ColumnsImagePlaceholder';
import {ImageHoverControls} from '../../block-editor/ImageHoverControls';

const {MediaUpload, MediaUploadCheck, RichText} = wp.blockEditor;
const {Button} = wp.components;
const {__} = wp.i18n;

export const EditableColumns = ({
  columns_block_style,
  toAttribute,
  columns,
  isCampaign,
  columnImages,
}) => {
  const getImageOrButton = (openMediaModal, index) => {
    if ((0 < columns[index].attachment)) {
      return <div className="columns-image-container">
        <ImageHoverControls
          onEdit={openMediaModal}
          onRemove={() => toAttribute('attachment', index)(0)}
          isCompact={columns_block_style === LAYOUT_ICONS}
        />
        <img src={columnImages[columns[index].attachment]} alt="Attachment" />
      </div>;
    }

    if (columns_block_style === LAYOUT_TASKS) {
      return <Button
        onClick={openMediaModal}
        icon="plus-alt2"
        isPrimary
        className="tasks-image-button"
      >
        {__('Add image', 'planet4-master-theme-backend')}
      </Button>;
    }

    return <div className="image-placeholder-container">
      <ColumnsImagePlaceholder
        width={columns_block_style !== LAYOUT_ICONS ? '100%' : 100}
        height={columns_block_style !== LAYOUT_ICONS ? 250 : 100}
      />
      <Button
        onClick={openMediaModal}
        icon="plus-alt2"
        isPrimary
        className="image-placeholder-button"
      >
        {/* For the Icons style we only show the button icon */}
        {columns_block_style !== LAYOUT_ICONS ? __('Add image', 'planet4-master-theme-backend') : ''}
      </Button>
    </div>;
  };

  return (
    <div className={columns_block_style === LAYOUT_TASKS ? 'tasks-wrap can-do-steps d-lg-block' : ''}>
      <div className="row">
        {columns.map((column, index) => (
          <div key={`column-${index}`} className={`col-md-6 col-lg column-wrap ${columns_block_style === LAYOUT_TASKS ? 'step-info' : ''}`}>
            {columns_block_style === LAYOUT_TASKS && (
              <span className="step-number">
                <span className="step-number-inner">{index + 1}</span>
              </span>
            )}
            {[LAYOUT_ICONS, LAYOUT_IMAGES].includes(columns_block_style) && (
              <div className="attachment-container">
                <MediaUploadCheck>
                  <MediaUpload
                    type="image"
                    onSelect={({id}) => toAttribute('attachment', index)(id)}
                    value={column.attachment}
                    allowedTypes={columns_block_style === LAYOUT_ICONS ? ['image/png'] : ['image']}
                    render={({open}) => getImageOrButton(open, index)}
                  />
                </MediaUploadCheck>
                {columns_block_style === LAYOUT_ICONS && column.attachment > 0 && typeof columnImages[column.attachment] !== 'undefined' && !columnImages[column.attachment].endsWith('.png') &&
                  <div className="column-image-error">
                    {__('Please select another image for this column, as the current image is not an icon and you have chosen columns style icons. ', 'planet4-master-theme-backend')}
                  </div>
                }
              </div>
            )}
            <RichText
              tagName={columns_block_style === LAYOUT_TASKS ? 'h5' : 'h3'}
              placeholder={__('Enter column header', 'planet4-master-theme-backend')}
              value={column.title}
              onChange={toAttribute('title', index)}
              withoutInteractiveFormatting
              allowedFormats={[]}
            />
            <RichText
              tagName="p"
              placeholder={__('Enter column description', 'planet4-master-theme-backend')}
              value={column.description}
              onChange={toAttribute('description', index)}
              withoutInteractiveFormatting
              allowedFormats={['core/bold', 'core/italic']}
            />
            {columns_block_style === LAYOUT_TASKS && (
              <MediaUploadCheck>
                <MediaUpload
                  type="image"
                  onSelect={({id}) => toAttribute('attachment', index)(id)}
                  value={column.attachment}
                  allowedTypes={['image']}
                  render={({open}) => getImageOrButton(open, index)}
                />
              </MediaUploadCheck>
            )}
            <RichText
              tagName="div"
              className={isCampaign || [LAYOUT_NO_IMAGE, LAYOUT_TASKS].includes(columns_block_style) ?
                `btn btn-${isCampaign ? 'primary' : 'secondary'} ${columns_block_style === LAYOUT_TASKS ? 'btn-small' : ''}` :
                'standalone-link'}
              placeholder={[LAYOUT_NO_IMAGE, LAYOUT_TASKS].includes(columns_block_style) ?
                __('Enter column button text', 'planet4-master-theme-backend') :
                __('Enter column link text', 'planet4-master-theme-backend')}
              value={column.cta_text}
              onChange={toAttribute('cta_text', index)}
              withoutInteractiveFormatting
              allowedFormats={[]}
            />
          </div>
        ))}
      </div>
    </div>
  );
};
