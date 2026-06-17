import {OpenGraphSidebar} from './Sidebar/OpenGraphSidebar';
import {PageHeaderSidebar} from './Sidebar/PageHeaderSidebar';
import {ActionSidebar} from './Sidebar/ActionSidebar';
import {CampaignSidebar} from './Sidebar/CampaignSidebar';
import {SearchEngineOptimizationsSidebar} from './Sidebar/SearchEngineOptimizationsSidebar';
import {AnalyticsTrackingSidebar} from './Sidebar/AnalyticsTrackingSidebar';

const {registerPlugin} = wp.plugins;

const sidebarsForPostType = postType => {
  switch (postType) {
  case 'campaign':
    return [
      PageHeaderSidebar,
      CampaignSidebar,
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
  let registeredSidebars = [];
  // Only subscribing after DOMContentLoaded avoids the troubles originating from wp.data emitting null values before that point.
  document.addEventListener('DOMContentLoaded', () => {
    wp.data.subscribe(() => {
      const newPostType = wp.data.select('core/editor').getCurrentPostType();
      if (newPostType === currentPostType) {
        return;
      }

      currentPostType = newPostType;

      // Unregister all sidebars registered for the previous post type before registering
      // new ones. This is necessary because sidebars are registered globally via wp.plugins
      // and persist across post type changes. Without this cleanup, sidebars built for
      // editorial post types (post, page, p4_action, campaign) would remain active when
      // switching to design-artifact post types like wp_template or wp_template_part,
      // where their expected meta fields are absent causing editor crashes.
      registeredSidebars.forEach(sidebar => wp.plugins.unregisterPlugin(sidebar.getId()));
      registeredSidebars = [];

      const sidebars = sidebarsForPostType(newPostType);
      if (!sidebars) {
        return;
      }

      registeredSidebars = sidebars;
      sidebars.forEach(sidebar => registerPlugin(sidebar.getId(), {
        icon: sidebar.getIcon ? sidebar.getIcon() : '',
        render: sidebar.render,
      }));
    });
  });
};
