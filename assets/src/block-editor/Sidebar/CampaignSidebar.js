import {PluginDocumentSettingPanel} from '@wordpress/edit-post';
import {NavigationType} from '../NavigationType/NavigationType';
import {getSidebarFunctions} from './getSidebarFunctions';

const FIELD_NAVTYPE = 'campaign_nav_type';

const {__} = wp.i18n;

/**
 * Add settings to Campaign pages
 */
export const CampaignSidebar = {
  getId: () => 'planet4-campaign-sidebar',
  render: () => {
    const {getParams} = getSidebarFunctions();

    return (
      <PluginDocumentSettingPanel
        name="navigation-panel"
        title={__('Navigation', 'planet4-blocks-backend')}
        className="navigation-panel"
      >
        <NavigationType {...getParams(FIELD_NAVTYPE)} />
      </PluginDocumentSettingPanel>
    );
  },
};
