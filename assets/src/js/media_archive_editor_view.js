import {createRoot} from 'react-dom/client';
import ArchivePicker, {EDITOR_VIEW} from '../js/Components/ArchivePicker/ArchivePicker';

const {__} = wp.i18n;
const frame = wp.media.view.MediaFrame.Select;

(function() {
  wp.media.view.MediaFrame.Select = frame.extend({
    initialize() {
      frame.prototype.initialize.apply(this, arguments);

      const State = wp.media.controller.State.extend({
        insert() {
          this.frame.close();
        },
      });

      this.states.add([
        new State({
          id: 'mediaArchive',
          search: false,
          title: __('Greenpeace Media', 'planet4-master-theme-backend'),
        }),
      ]);

      this.on('content:render:mediaArchive', this.renderMediaArchive, this);
    },

    browseRouter(routerView) {
      routerView.set({
        upload: {
          text: wp.media.view.l10n.uploadFilesTitle,
          priority: 20,
        },
        mediaArchive: {
          text: __('Greenpeace Media', 'planet4-master-theme-backend'),
          priority: 30,
        },
        browse: {
          text: wp.media.view.l10n.mediaLibraryTitle,
          priority: 40,
        },
      });
    },

    renderMediaArchive() {
      const MediaArchiveContent = wp.Backbone.View.extend({
        tagName: 'div',
        className: 'media-archive-content',
        template: _.template('<div id=\'media-archive-div\'></div>'), // eslint-disable-line no-undef
        render() {
          const domNode = this.$('#media-archive-div').prevObject.get(0);
          const root = createRoot(domNode);
          root.render(<ArchivePicker view={EDITOR_VIEW} />);
        },
        active: false,
        toolbar: null,
        frame: null,
      });

      this.content.set(new MediaArchiveContent());
    },
  });
}());
