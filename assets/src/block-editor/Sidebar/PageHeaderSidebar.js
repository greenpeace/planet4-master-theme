import {CheckboxSidebarField} from '../SidebarFields/CheckboxSidebarField';
import {getSidebarFunctions} from './getSidebarFunctions';

const {__} = wp.i18n;
const {PluginDocumentSettingPanel} = wp.editor;

// The various meta fields
const HIDE_PAGE_TITLE = 'p4_hide_page_title_checkbox';

/**
 * Page header settings for the sidebar
 */
export const PageHeaderSidebar = {
  getId: () => 'planet4-page-header-sidebar',
  render: () => {
    const {getParams} = getSidebarFunctions();

    return (
      <PluginDocumentSettingPanel
        name="page-header-panel"
        title={__('Page header', 'planet4-blocks-backend')}
      >
        <CheckboxSidebarField label={__('Hide page title', 'planet4-blocks-backend')} {...getParams(HIDE_PAGE_TITLE)} />
      </PluginDocumentSettingPanel>
    );
  },
};

