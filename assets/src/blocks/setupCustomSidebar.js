import {registerPlugin} from '@wordpress/plugins';
import {OpenGraphSidebar} from '../components/Sidebar/OpenGraphSidebar';

const sidebarsForPostType = postType => {
  switch (postType) {
  case 'campaign':
    return [
      OpenGraphSidebar,
    ];
  case 'p4_action':
    return [
      OpenGraphSidebar,
    ];
  case 'page':
    return [
      OpenGraphSidebar,
    ];
  case 'post':
    return [
      OpenGraphSidebar,
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
