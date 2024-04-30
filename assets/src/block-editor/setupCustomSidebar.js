import {registerPlugin} from '@wordpress/plugins';
import {OpenGraphSidebar} from './Sidebar/OpenGraphSidebar';
import {PageHeaderSidebar} from './Sidebar/PageHeaderSidebar';
import {ActionSidebar} from './Sidebar/ActionSidebar';
import {CampaignSidebar} from './Sidebar/CampaignSidebar';
import {SearchEngineOptimizationsSidebar} from './Sidebar/SearchEngineOptimizationsSidebar';
import {AnalyticsTrackingSidebar} from './Sidebar/AnalyticsTrackingSidebar';

const sidebarsForPostType = postType => {
  switch (postType) {
  case 'campaign':
    return [
      PageHeaderSidebar,
      OpenGraphSidebar,
      SearchEngineOptimizationsSidebar,
      AnalyticsTrackingSidebar,
    ];
  case 'p4_action':
    return [
      ActionSidebar,
      OpenGraphSidebar,
      SearchEngineOptimizationsSidebar,
      AnalyticsTrackingSidebar,
    ];
  case 'page':
    return [
      PageHeaderSidebar,
      OpenGraphSidebar,
      SearchEngineOptimizationsSidebar,
      AnalyticsTrackingSidebar,
    ];
  case 'post':
    return [
      OpenGraphSidebar,
      SearchEngineOptimizationsSidebar,
      AnalyticsTrackingSidebar,
    ];
  default:
    return null;
  }
};

export const setupCustomSidebar = () => {
  let currentPostType = null;
  // Only subscribing after DOMContentLoaded avoids the troubles originating from wp.data emitting null values before that point.
  document.addEventListener('DOMContentLoaded', () => {
    wp.data.subscribe(() => {
      const newPostType = wp.data.select('core/editor').getCurrentPostType();
      if (newPostType === currentPostType) {
        return;
      }

      currentPostType = newPostType;
      const sidebars = sidebarsForPostType(newPostType);
      if (!sidebars) {
        return;
      }

      sidebars.forEach(sidebar => registerPlugin(sidebar.getId(), {
        icon: sidebar.getIcon ? sidebar.getIcon() : '',
        render: sidebar.render,
      }));
    });
  });
};
