import { SingleSidebar } from './SingleSidebar';
import { MultiSidebar } from './MultiSidebar';

export const archivePickerSidebar = ( archivePicker ) => ( imagePicker ) => {
  const selectedImages = imagePicker.getSelectedImages();

  if ( selectedImages.length === 1 ) {
    return <SingleSidebar
      image={ selectedImages[ 0 ] }
      includeInWp={ archivePicker.includeInWp }
      processingError={ archivePicker.state.processingError }
      processingImages={ archivePicker.state.processingImages }
    />;
  }

  if ( selectedImages.length > 1 ) {
    return <MultiSidebar
      parent={ imagePicker }
      includeInWp={ archivePicker.includeInWp }
    />;
  }
};
