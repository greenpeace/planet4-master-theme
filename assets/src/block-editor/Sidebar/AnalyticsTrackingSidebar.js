import {useEffect} from '@wordpress/element';
import {PluginDocumentSettingPanel} from '@wordpress/edit-post';
import {dispatch, useSelect} from '@wordpress/data';
import {SelectSidebarField} from '../SidebarFields/SelectSidebarField';
import {TextSidebarField} from '../SidebarFields/TextSidebarField';
import {getSidebarFunctions} from './getSidebarFunctions';

const {__} = wp.i18n;

export const AnalyticsTrackingSidebar = {
  getId: () => 'planet4-analytics-sidebar',
  render: () => {
    const {getParams} = getSidebarFunctions();
    const postId = wp.data.select('core/editor').getCurrentPostId();
    const options = useSelect(select => {
      return select('core').getEntityRecord('planet4/v1', 'analytics-values');
    });

    useEffect(() => {
      dispatch('core').addEntities([{
        baseURL: '/planet4/v1/analytics-values',
        baseURLParams: {post_id: postId},
        kind: 'planet4/v1',
        name: 'analytics-values',
        label: 'Analytics and tracking values',
      }]);
    }, [postId]);

    return options && (
      <PluginDocumentSettingPanel
        name="analytics-panel"
        title={__('Analytics & Tracking', 'planet4-blocks-backend')}
      >
        <SelectSidebarField
          label={__('Global Project', 'planet4-master-theme-backend')}
          options={options.global_projects || []}
          {...getParams('p4_campaign_name')} />
        <SelectSidebarField
          label={__('Local Projects', 'planet4-master-theme-backend')}
          options={options.local_projects || []}
          {...getParams('p4_local_project')} />
        <SelectSidebarField
          label={__('Basket Name', 'planet4-master-theme-backend')}
          options={options.baskets || []}
          {...getParams('p4_basket_name')} />
        <TextSidebarField
          label={__('Department', 'planet4-master-theme-backend')}
          {...getParams('p4_department')} />
      </PluginDocumentSettingPanel>
    );
  },
};
