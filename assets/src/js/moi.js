import {createRoot} from '@wordpress/element';
import ArchivePicker from './Components/ArchivePicker';

var frame = wp.media.view.MediaFrame.Select;

const mediaArchiveTab = () => {
  wp.media.view.MediaFrame.Select = frame.extend({
    initialize: function() {
      frame.prototype.initialize.apply(this, arguments);

      var State = wp.media.controller.State.extend({
        insert: function() {
          this.frame.close();
        }
      });

      this.states.add([
        new State({
          id: 'mediaArchive1',
          search: false,
          title: 'Media Archive',
        })
      ]);

      this.on('content:render:mediaArchive1', this.renderMediaArchive, this);
    },

    browseRouter: function(routerView) {
      routerView.set({
        upload: {
          text: wp.media.view.l10n.uploadFilesTitle,
          priority: 20
        },
        mediaArchive1: {
          text: 'Media Archive',
          priority: 30
        },
        browse: {
          text: wp.media.view.l10n.mediaLibraryTitle,
          priority: 40
        }
      });
    },

    renderMediaArchive : function() {
      var MediaArchiveContent = wp.Backbone.View.extend({
        tagName: 'div',
        className: 'media-archive-content',
        template: _.template("<div id='user-info'></div>"),
        render() {
          var domeNode = this.$("#user-info").prevObject.get(0);
          var root = createRoot(domeNode);
          root.render(<ArchivePicker />);
        },
        // remove() {
        //   // ReactDOM.unmountComponentAtNode(this.el);
        //   // Backbone.View.prototype.remove.call(this);
        // },
        active: false,
        toolbar: null,
        frame: null,
      });

      var view = new MediaArchiveContent();

      this.content.set(view);
    }
  });
};

mediaArchiveTab();
