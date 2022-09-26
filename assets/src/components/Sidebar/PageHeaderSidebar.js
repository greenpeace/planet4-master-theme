import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { CheckboxSidebarField } from '../SidebarFields/CheckboxSidebarField';
import { TextareaSidebarField } from '../SidebarFields/TextareaSidebarField';
import { ImageSidebarField } from '../SidebarFields/ImageSidebarField';
import { TextSidebarField } from '../SidebarFields/TextSidebarField';

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

/**
 * Page header settings for the sidebar
 */
 export const PageHeaderSidebar = {
  getId: () => 'planet4-page-header-sidebar',
  render: () => {
    const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta'),[]);
    const postType = useSelect(select => select('core/editor').getCurrentPostType());
    const isCampaign = postType === 'campaign';

    const { editPost } = useDispatch('core/editor');

    const updateValueAndDependencies = fieldId => value => editPost({ meta: {[fieldId]: value} });

    const getParams = name => ({
      value: meta[name] || '',
      setValue: updateValueAndDependencies(name),
    });

    const imageParams = {
      value: {
        id: meta[BACKGROUND_IMAGE_ID] || '',
        url: meta[BACKGROUND_IMAGE_URL] || '',
      },
      setValue: (id, url) => {
        updateValueAndDependencies(BACKGROUND_IMAGE_ID)(id);
        updateValueAndDependencies(BACKGROUND_IMAGE_URL)(url);
      }
    };

    return (
      <PluginDocumentSettingPanel
        name='page-header-panel'
        title={ __( 'Page header', 'planet4-blocks-backend' ) }
      >
        <TextSidebarField label={__( 'Header title', 'planet4-blocks-backend' )} {...getParams(HEADER_TITLE)} />
        <TextSidebarField label={__( 'Header subtitle', 'planet4-blocks-backend' )} {...getParams(HEADER_SUBTITLE)} />
        <TextareaSidebarField label={__( 'Header description', 'planet4-blocks-backend' )} {...getParams(HEADER_DESCRIPTION)} />
        {!isCampaign && (
          <>
            <TextSidebarField label={__( 'Header button title', 'planet4-blocks-backend' )} {...getParams(HEADER_BUTTON_TITLE)} />
            <TextSidebarField label={__( 'Header button link', 'planet4-blocks-backend' )} {...getParams(HEADER_BUTTON_LINK)} />
            <CheckboxSidebarField label={__( 'Open header button link in new tab', 'planet4-blocks-backend' )} {...getParams(HEADER_BUTTON_NEW_TAB)} />
          </>
        )}
        <ImageSidebarField label={__('Background image override', 'planet4-blocks-backend')} {...imageParams} />
        <CheckboxSidebarField label={__( 'Hide page title', 'planet4-blocks-backend' )} {...getParams(HIDE_PAGE_TITLE)} />
      </PluginDocumentSettingPanel>
    );
  }
}

