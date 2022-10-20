import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { NavigationType } from '../NavigationType/NavigationType';
import { CheckboxSidebarField } from '../SidebarFields/CheckboxSidebarField';
import { TextSidebarField } from '../SidebarFields/TextSidebarField';

const FIELD_NAVTYPE = 'nav_type';
const HIDE_PAGE_TITLE = 'p4_hide_page_title_checkbox';
const BUTTON_TEXT = 'action_button_text';

/**
 * Add settings to Action pages
 */
export const ActionSidebar = {
  getId: () => 'planet4-action-sidebar',
  render: () => {
    const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta'),[]);
    const { editPost } = useDispatch('core/editor');
    const updateValueAndDependencies = fieldId => value => editPost({ meta: {[fieldId]: value} });

    const navParams = {
      value: meta[FIELD_NAVTYPE] || null,
      setValue: updateValueAndDependencies(FIELD_NAVTYPE),
    };

    const hidePageTitleParams = {
      value: meta[HIDE_PAGE_TITLE] || '',
      setValue: updateValueAndDependencies(HIDE_PAGE_TITLE),
    }

    const buttonTextParams = {
      value: meta[BUTTON_TEXT],
      setValue: updateValueAndDependencies(BUTTON_TEXT),
    }

    return (
      <>
        <PluginDocumentSettingPanel
          name='page-header-panel'
          title={ __( 'Page header', 'planet4-blocks-backend' ) }
        >
          <CheckboxSidebarField label={__( 'Hide page title', 'planet4-blocks-backend' )} {...hidePageTitleParams} />
        </PluginDocumentSettingPanel>
        <PluginDocumentSettingPanel
          name='button-text-panel'
          title={ __( 'Covers block button text', 'planet4-blocks-backend' ) }
        >
          <TextSidebarField
            label={__( 'Edit the button text shown on the Action covers block', 'planet4-blocks-backend' )}
            {...buttonTextParams}
          />
        </PluginDocumentSettingPanel>
        <PluginDocumentSettingPanel
          name='navigation-panel'
          title={ __( 'Navigation', 'planet4-blocks-backend' ) }
          className='navigation-panel'
        >
          <NavigationType {...navParams} />
        </PluginDocumentSettingPanel>
      </>
    );
  }
}
