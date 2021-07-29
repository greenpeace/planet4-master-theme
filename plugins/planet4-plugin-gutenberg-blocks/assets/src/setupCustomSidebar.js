import { registerPlugin } from "@wordpress/plugins";
import { CampaignSidebar } from './components/Sidebar/CampaignSidebar';

const sidebarForPostType = ( postType ) => {
  switch ( postType ) {
    case 'campaign':
      return CampaignSidebar;
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

      const sidebarComponent = sidebarForPostType( newPostType );

      if ( sidebarComponent ) {
        registerPlugin( sidebarComponent.getId(), {
          icon: sidebarComponent.getIcon(),
          render: sidebarComponent
        } );
      }
    } );
  } );
};
