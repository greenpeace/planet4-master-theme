import { addScriptTag } from '../../components/useScript/addScriptTag';
import { addLinkTag } from '../../components/useStyleSheet/addLinkTag';

export const setupMediaElementJS = function() {
  const meJSNodes = document.querySelectorAll( '.mejs-video-block' );

  if (meJSNodes.length === 0) {
    return;
  }

  addLinkTag({ href: 'https://cdn.jsdelivr.net/npm/mediaelement@4.2.16/build/mediaelementplayer-legacy.min.css' });
  
	const onLoad = () => {
    meJSNodes.forEach(node => {
      const player = new MediaElementPlayer(node, {
        classPrefix: 'mejs-',
        alwaysShowControls: true,
      });
    })
	};

	addScriptTag({
		src: 'https://cdn.jsdelivr.net/npm/mediaelement@4.2.16/build/mediaelement-and-player.min.js',
		async: true,
		onLoad
	});
}
