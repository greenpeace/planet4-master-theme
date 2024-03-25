import {addScriptTag} from '../../hooks/useScript/addScriptTag';
import {addLinkTag} from '../../hooks/useStylesheet/addLinkTag';

export const setupMediaElementJS = function() {
  const meJSNodes = document.querySelectorAll('.mejs-video-block');

  if (meJSNodes.length === 0) {
    return;
  }

  addLinkTag({href: 'https://cdn.jsdelivr.net/npm/mediaelement@4.2.16/build/mediaelementplayer-legacy.min.css'});

  const onLoad = () => {
    meJSNodes.forEach(node => {
      // eslint-disable-next-line no-undef, no-unused-vars
      const player = new MediaElementPlayer(node, {
        classPrefix: 'mejs-',
        alwaysShowControls: true,
      });
    });
  };

  addScriptTag({
    src: 'https://cdn.jsdelivr.net/npm/mediaelement@4.2.16/build/mediaelement-and-player.min.js',
    async: true,
    onLoad,
  });
};
