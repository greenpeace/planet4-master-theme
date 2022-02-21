import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { NavigationType } from '../NavigationType/NavigationType';

const FIELD_NAVTYPE = 'nav_type';

/**
 * Add Document settings to Action pages
 */
export const ActionSidebar = (attributes) => {
  const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta'),[]);
  const { editPost } = useDispatch('core/editor');
  const updateValueAndDependencies = fieldId => value => {
    editPost({ meta: {[fieldId]: value} });
  }

  const navParams = {
    value: meta[FIELD_NAVTYPE] || null,
    setValue: updateValueAndDependencies(FIELD_NAVTYPE),
  };

  return (
    <PluginDocumentSettingPanel
      name="navigation-panel"
      title={ __( "Navigation", 'planet4-blocks-backend' ) }
      className="navigation-panel"
    >
      <NavigationType {...navParams} />
    </PluginDocumentSettingPanel>
  );
}
