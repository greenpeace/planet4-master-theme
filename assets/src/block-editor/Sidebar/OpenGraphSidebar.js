import {PluginDocumentSettingPanel} from '@wordpress/editor';
import {TextareaSidebarField} from '../SidebarFields/TextareaSidebarField';
import {ImageSidebarField} from '../SidebarFields/ImageSidebarField';
import {TextSidebarField} from '../SidebarFields/TextSidebarField';
import {getSidebarFunctions} from './getSidebarFunctions';

// The various meta fields
const OG_TITLE = 'p4_og_title';
const OG_DESCRIPTION = 'p4_og_description';
const OG_IMAGE_ID = 'p4_og_image_id';
const OG_IMAGE_URL = 'p4_og_image';

const {__} = wp.i18n;

/**
 * Open Graph settings for the sidebar
 */
export const OpenGraphSidebar = {
  getId: () => 'planet4-open-graph-sidebar',
  render: () => {
    const {getParams, getImageParams} = getSidebarFunctions();

    return (
      <PluginDocumentSettingPanel
        name="open-graph-panel"
        title={__('Open Graph/Social Fields', 'planet4-blocks-backend')}
      >
        <TextSidebarField label={__('Title', 'planet4-blocks-backend')} {...getParams(OG_TITLE)} />
        <TextareaSidebarField label={__('Description', 'planet4-blocks-backend')} {...getParams(OG_DESCRIPTION)} />
        <ImageSidebarField label={__('Image override', 'planet4-blocks-backend')} {...getImageParams(OG_IMAGE_ID, OG_IMAGE_URL)} />
      </PluginDocumentSettingPanel>
    );
  },
};

