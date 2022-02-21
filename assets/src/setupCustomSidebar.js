import { registerPlugin } from "@wordpress/plugins";
import { CampaignSidebar } from './components/Sidebar/CampaignSidebar';
import { ActionSidebar } from './components/Sidebar/ActionSidebar';

const sidebarForPostType = ( postType ) => {
  switch ( postType ) {
    case 'campaign':
      return {
        id: CampaignSidebar.getId(),
        icon: CampaignSidebar.getIcon(),
        render: CampaignSidebar,
      };
    case 'p4_action':
      return {
        id: 'planet4-action-sidebar',
        render: ActionSidebar,
      };
    default:
      return null;
  }
};

export const setupCustomSidebar = () => {
  let currentPostType = null;
  // Only subscribing after DOMContentLoaded avoids the troubles originating from wp.data emitting null values before that point.
  document.addEventListener( 'DOMContentLoaded', event => {
    wp.data.subscribe( () => {
      const newPostType = wp.data.select( 'core/editor' ).getCurrentPostType();
      if ( newPostType === currentPostType ) {
        return;
      }

      currentPostType = newPostType;
      const sidebar = sidebarForPostType( newPostType );
      if ( ! sidebar ) {
        return;
      }

      registerPlugin( sidebar.id, {
        icon: sidebar.icon || '',
        render: sidebar.render
      } );
    } );
  } );
};
