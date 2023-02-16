import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { SelectControl, TextControl } from '@wordpress/components';
import { dispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { SelectSidebarField } from '../SidebarFields/SelectSidebarField';
import { TextSidebarField } from '../SidebarFields/TextSidebarField';
import { getSidebarFunctions } from './getSidebarFunctions';

dispatch('core').addEntities( [{
    baseURL: '/planet4/v1/analytics-values',
    kind: 'planet4/v1',
    name: 'analytics-values',
    label: 'Analytics and tracking values',
}] );

const GLOBAL_PROJECT = 'p4_campaign_name';
const LOCAL_PROJECT = 'p4_local_project';
const BASKET = 'p4_basket_name';
const DEPARTMENT = 'p4_department';

export const AnalyticsTrackingSidebar = {
  getId: () => 'planet4-analytics-sidebar',
  render: () => {
    const { getParams } = getSidebarFunctions();
    const options = useSelect((select) => {
      return select('core').getEntityRecords('planet4/v1', 'analytics-values');
    });

    const globalOptions = options ? options[0]['global_projects'] : [];
    const localOptions = options ? options[0]['local_projects'] : [];
    const basketOptions = options ? options[0]['baskets'] : [];

    return (
      <>
        <PluginDocumentSettingPanel
          name='analytics-panel'
          title={ __( 'Analytics & Tracking', 'planet4-blocks-backend' ) }
        >
          <SelectSidebarField
            label={__('Global Project', 'planet4-master-theme-backend')}
            options={globalOptions}
            {...getParams(GLOBAL_PROJECT)} />
          <SelectSidebarField
            label={__('Local Projects', 'planet4-master-theme-backend')}
            options={localOptions}
            {...getParams(LOCAL_PROJECT)} />
          <SelectSidebarField
            label={__('Basket Name', 'planet4-master-theme-backend')}
            options={basketOptions}
            {...getParams(BASKET)} />
          <TextSidebarField
            label={__('Department', 'planet4-master-theme-backend')}
            {...getParams(DEPARTMENT)} />
        </PluginDocumentSettingPanel>
      </>
    );
  }
}
