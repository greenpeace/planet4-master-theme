import { registerPlugin } from '@wordpress/plugins';
import { PageHeaderSidebar } from './components/Sidebar/PageHeaderSidebar';
import { CampaignThemeSidebar } from './components/Sidebar/CampaignThemeSidebar';
import { ActionSidebar } from './components/Sidebar/ActionSidebar';
import { OpenGraphSidebar } from './components/Sidebar/OpenGraphSidebar';
import { SearchEngineOptimizationsSidebar } from './components/Sidebar/SearchEngineOptimizationsSidebar';

const sidebarsForPostType = postType => {
  switch (postType) {
  case 'campaign':
    return [
      PageHeaderSidebar,
      {
        getId: CampaignThemeSidebar.getId,
        getIcon: CampaignThemeSidebar.getIcon,
        render: CampaignThemeSidebar,
      },
      OpenGraphSidebar,
      SearchEngineOptimizationsSidebar,
    ];
  case 'p4_action':
    return [
      ActionSidebar,
      OpenGraphSidebar,
      SearchEngineOptimizationsSidebar,
    ];
  case 'page':
    return [
      PageHeaderSidebar,
      OpenGraphSidebar,
      SearchEngineOptimizationsSidebar,
    ];
  case 'post':
    return [
      OpenGraphSidebar,
      SearchEngineOptimizationsSidebar,
    ];
  default:
    return null;
  }
};

export const setupCustomSidebar = () => {
  let currentPostType = null;
  // Only subscribing after DOMContentLoaded avoids the troubles originating from wp.data emitting null values before that point.
  document.addEventListener( 'DOMContentLoaded', () => {
    wp.data.subscribe( () => {
      const newPostType = wp.data.select( 'core/editor' ).getCurrentPostType();
      if ( newPostType === currentPostType ) {
        return;
      }

      currentPostType = newPostType;
      const sidebars = sidebarsForPostType( newPostType );
      if ( ! sidebars ) {
        return;
      }

      sidebars.forEach(sidebar => registerPlugin( sidebar.getId(), {
        icon: sidebar.getIcon ? sidebar.getIcon() : '',
        render: sidebar.render
      } ));
    } );
  } );
};
