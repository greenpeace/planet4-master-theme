import {NavigationType} from '../NavigationType/NavigationType';
import {CheckboxSidebarField} from '../SidebarFields/CheckboxSidebarField';
import {TextSidebarField} from '../SidebarFields/TextSidebarField';
import {getSidebarFunctions} from './getSidebarFunctions';

const FIELD_NAVTYPE = 'nav_type';
const HIDE_PAGE_TITLE = 'p4_hide_page_title_checkbox';
const BUTTON_TEXT = 'action_button_text';
const BUTTON_ACCESSIBILITY_TEXT = 'action_button_accessibility_text';

const {__} = wp.i18n;
const {PluginDocumentSettingPanel} = wp.editor;

/**
 * Add settings to Action pages
 */
export const ActionSidebar = {
  getId: () => 'planet4-action-sidebar',
  render: () => {
    const {getParams} = getSidebarFunctions();

    return (
      <>
        <PluginDocumentSettingPanel
          name="page-header-panel"
          title={__('Page header', 'planet4-blocks-backend')}
        >
          <CheckboxSidebarField label={__('Hide page title', 'planet4-blocks-backend')} {...getParams(HIDE_PAGE_TITLE)} />
        </PluginDocumentSettingPanel>
        <PluginDocumentSettingPanel
          name="button-text-panel"
          title={__('Action Lists block button text', 'planet4-blocks-backend')}
        >
          <TextSidebarField
            label={__('Set the text for the Actions List Block\'s Button (e.g., \'Sign Up\')', 'planet4-blocks-backend')}
            {...getParams(BUTTON_TEXT)}
          />
          <TextSidebarField
            label={__('Add descriptive text for screen readers (e.g., \'Sign Up to Stop Meat and Dairy\').', 'planet4-blocks-backend')}
            {...getParams(BUTTON_ACCESSIBILITY_TEXT)}
          />
        </PluginDocumentSettingPanel>
        <PluginDocumentSettingPanel
          name="navigation-panel"
          title={__('Navigation', 'planet4-blocks-backend')}
          className="navigation-panel"
        >
          <NavigationType {...getParams(FIELD_NAVTYPE)} />
        </PluginDocumentSettingPanel>
      </>
    );
  },
};
