import {NavigationType} from '../NavigationType/NavigationType';
import {getSidebarFunctions} from './getSidebarFunctions';

const {__} = wp.i18n;
const {PluginDocumentSettingPanel} = wp.editor;

const FIELD_NAVTYPE = 'campaign_nav_type';

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
        title={__('Navigation', 'planet4-master-theme-backend')}
        className="navigation-panel"
      >
        <NavigationType {...getParams(FIELD_NAVTYPE)} />
      </PluginDocumentSettingPanel>
    );
  },
};
