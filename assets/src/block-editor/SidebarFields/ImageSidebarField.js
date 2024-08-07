import {MediaUpload, MediaUploadCheck} from '@wordpress/block-editor';
import {ImageHoverControls} from '../ImageHoverControls';

const {Button} = wp.components;
const {__} = wp.i18n;

export const ImageSidebarField = ({value, setValue, label}) => (
  <div className="components-base-control mb-3">
    <label className="components-base-control__label mb-2 d-block" htmlFor={value.id}>
      {label}
    </label>
    <MediaUploadCheck>
      <MediaUpload
        title={label}
        type="image"
        value={value.id}
        onSelect={({id, url}) => setValue(id.toString(), url)}
        allowedTypes={['image']}
        render={({open}) => value.url ?
          <div style={{position: 'relative'}} id={value.id}>
            <ImageHoverControls
              onEdit={open}
              onRemove={() => setValue('', '')}
            />
            <img src={value.url} alt="" />
          </div> :
          <Button
            onClick={open}
            className="button"
          >
            {__('Select image', 'planet4-blocks-backend')}
          </Button>}
      />
    </MediaUploadCheck>
  </div>
);
