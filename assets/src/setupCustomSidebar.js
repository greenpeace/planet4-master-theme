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
  // `wp.data.select( 'core/editor' ).getCurrentPostType()` will return null a few times
  // before it actually starts working.
  let currentPostType = null;
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
};
