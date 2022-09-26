import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { ImageHoverControls } from '../ImageHoverControls';
import { __ } from '@wordpress/i18n';

export const ImageSidebarField = ({ value, setValue, label }) => (
  <div className="components-base-control mb-3">
    <label className="components-base-control__label mb-2">
      {label}
    </label>
    <MediaUploadCheck>
      <MediaUpload
        title={label}
        type='image'
        value={value.id}
        onSelect={({ id, url }) => setValue(id.toString(), url)}
        allowedTypes={[ 'image' ]}
        render={({ open }) => value.url ?
          <div style={{ position: 'relative' }}>
            <ImageHoverControls
              onEdit={open}
              onRemove={() => setValue('', '')}
            />
            <img src={value.url} />
          </div> :
          <Button
            onClick={open}
            className='button'
          >
            {__('Select image', 'planet4-blocks-backend')}
          </Button>
        }
      />
    </MediaUploadCheck>
  </div>
);
