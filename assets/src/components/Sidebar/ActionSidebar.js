import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { NavigationType } from '../NavigationType/NavigationType';
import { CheckboxSidebarField } from '../SidebarFields/CheckboxSidebarField';

const FIELD_NAVTYPE = 'nav_type';
const HIDE_PAGE_TITLE = 'p4_hide_page_title_checkbox';

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

    return (
      <>
        <PluginDocumentSettingPanel
          name='page-header-panel'
          title={ __( 'Page header', 'planet4-blocks-backend' ) }
        >
          <CheckboxSidebarField label={__( 'Hide page title', 'planet4-blocks-backend' )} {...hidePageTitleParams} />
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
