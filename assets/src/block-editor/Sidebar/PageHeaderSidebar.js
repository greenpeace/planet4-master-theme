import {PluginDocumentSettingPanel} from '@wordpress/edit-post';
import {useSelect} from '@wordpress/data';
import {CheckboxSidebarField} from '../SidebarFields/CheckboxSidebarField';
import {TextareaSidebarField} from '../SidebarFields/TextareaSidebarField';
import {ImageSidebarField} from '../SidebarFields/ImageSidebarField';
import {TextSidebarField} from '../SidebarFields/TextSidebarField';
import {getSidebarFunctions} from './getSidebarFunctions';

// The various meta fields
const HIDE_PAGE_TITLE = 'p4_hide_page_title_checkbox';
const HEADER_TITLE = 'p4_title';
const HEADER_SUBTITLE = 'p4_subtitle';
const HEADER_DESCRIPTION = 'p4_description';
const BACKGROUND_IMAGE_ID = 'background_image_id';
const BACKGROUND_IMAGE_URL = 'background_image';
const HEADER_BUTTON_TITLE = 'p4_button_title';
const HEADER_BUTTON_LINK = 'p4_button_link';
const HEADER_BUTTON_NEW_TAB = 'p4_button_link_checkbox';

const {__} = wp.i18n;

/**
 * Page header settings for the sidebar
 */
export const PageHeaderSidebar = {
  getId: () => 'planet4-page-header-sidebar',
  render: () => {
    const postType = useSelect(select => select('core/editor').getCurrentPostType());
    const isCampaign = postType === 'campaign';

    const {getParams, getImageParams} = getSidebarFunctions();

    return (
      <PluginDocumentSettingPanel
        name="page-header-panel"
        title={__('Page header', 'planet4-blocks-backend')}
      >
        <TextSidebarField label={__('Header title', 'planet4-blocks-backend')} {...getParams(HEADER_TITLE)} />
        <TextSidebarField label={__('Header subtitle', 'planet4-blocks-backend')} {...getParams(HEADER_SUBTITLE)} />
        <TextareaSidebarField label={__('Header description', 'planet4-blocks-backend')} {...getParams(HEADER_DESCRIPTION)} />
        {!isCampaign && (
          <>
            <TextSidebarField label={__('Header button title', 'planet4-blocks-backend')} {...getParams(HEADER_BUTTON_TITLE)} />
            <TextSidebarField label={__('Header button link', 'planet4-blocks-backend')} {...getParams(HEADER_BUTTON_LINK)} />
            <CheckboxSidebarField label={__('Open header button link in new tab', 'planet4-blocks-backend')} {...getParams(HEADER_BUTTON_NEW_TAB)} />
          </>
        )}
        <ImageSidebarField label={__('Background image override', 'planet4-blocks-backend')} {...getImageParams(BACKGROUND_IMAGE_ID, BACKGROUND_IMAGE_URL)} />
        <CheckboxSidebarField label={__('Hide page title', 'planet4-blocks-backend')} {...getParams(HIDE_PAGE_TITLE)} />
      </PluginDocumentSettingPanel>
    );
  },
};

