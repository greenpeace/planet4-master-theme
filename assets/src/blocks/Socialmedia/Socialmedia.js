import {Component, RawHTML, Fragment} from '@wordpress/element';
import {Preview} from '../../components/Preview';
import {
  RadioControl,
  TextControl,
  TextareaControl,
  SelectControl,
  ServerSideRender
} from '@wordpress/components';


const {apiFetch} = wp;
const {addQueryArgs} = wp.url;

export class Socialmedia extends Component {
  constructor(props) {
    super(props);
  }

  componentDidMount() {
    this.checkTwitterScript();
    this.checkInstagramScript();
  }

  componentDidUpdate() {
    this.checkTwitterScript();
    this.checkInstagramScript();
  }

  /**
   * Check if twitter embeds script is loaded and initiliaze it.
   */
  checkTwitterScript() {
    if (this.props.social_media_url.includes('twitter')) {
      let twitterScript = document.querySelector('script[src="https://platform.twitter.com/widgets.js"]');

      if (null === twitterScript) {
        let scriptLoaded = this.loadScriptAsync('https://platform.twitter.com/widgets.js');
        scriptLoaded.then(function () {
          this.initializeTwitterEmbeds();
        }.bind(this));
      } else {
        this.initializeTwitterEmbeds();
      }
    }
  }

  /**
   * Check if instgram embeds script is loaded and initiliaze it.
   */
  checkInstagramScript() {
    if (this.props.social_media_url.includes('instagram')) {
      let instaScript = document.querySelector('script[src="https://www.instagram.com/embed.js"]');

      if (null === instaScript) {
        let scriptLoaded = this.loadScriptAsync('https://www.instagram.com/embed.js');
        scriptLoaded.then(function () {
          this.initializeInstagramEmbeds();
        }.bind(this));
      } else {
        this.initializeInstagramEmbeds();
      }
    }
  }

  /**
   * Initialize twitter embeds.
   */
  initializeTwitterEmbeds() {
    setTimeout(function () {
      if ('undefined' !== window.twttr) {
        window.twttr.widgets.load();
      }
    }, 2000);
  }

  /**
   * Initialize instagram embeds.
   */
  initializeInstagramEmbeds() {
    setTimeout(function () {
      if ('undefined' !== window.instgrm) {
        window.instgrm.Embeds.process();
      }
    }, 3000);
  }

  loadScriptAsync(uri) {
    return new Promise((resolve, reject) => {
      let tag = document.createElement('script');
      tag.src = uri;
      tag.async = true;
      tag.onload = () => {
        resolve();
      };
      let body = document.getElementsByTagName('body')[0];
      body.appendChild(tag);
    });
  };

  renderEdit() {
    const {__} = wp.i18n;

    const embed_type_help = __('Select oEmbed for the following types of social media<br>- Twitter: tweet, profile, list, collection, likes, moment<br>- Facebook: post, activity, photo, video, media, question, note<br>- Instagram: image', 'planet4-blocks-backend');

    return (
      <Fragment>
        <div>
          <TextControl
            label={__('Title', 'planet4-blocks-backend')}
            placeholder={__('Enter title', 'planet4-blocks-backend')}
            help={__('Optional', 'planet4-blocks-backend')}
            value={this.props.title}
            onChange={this.props.onTitleChange}
          />
        </div>

        <div>
          <TextareaControl
            label={__('Description', 'planet4-blocks-backend')}
            placeholder={__('Enter description', 'planet4-blocks-backend')}
            help={__('Optional', 'planet4-blocks-backend')}
            value={this.props.description}
            onChange={this.props.onDescriptionChange}
          />
        </div>

        <div>
          <RadioControl
            label={__('Embed type', 'planet4-blocks-backend')}
            help={
              <RawHTML>{embed_type_help}</RawHTML>
            }
            options={[
              {label: 'oEmbed', value: 'oembed'},
              {label: 'Facebook page', value: 'facebook_page'},
            ]}
            selected={this.props.embed_type}
            onChange={this.props.onEmbedTypeChange}
          />
        </div>

        {
          this.props.embed_type === 'facebook_page'
            ?
            <div>
              <SelectControl
                label={__('What Facebook page content would you like to display?', 'planet4-blocks-backend')}
                value={this.props.facebook_page_tab}
                options={[
                  {label: 'Timeline', value: 'timeline'},
                  {label: 'Events', value: 'events'},
                  {label: 'Mesages', value: 'messages'},
                ]}
                onChange={this.props.onFacebookPageTabChange}
              />
            </div>
            : null
        }

        <div>
          <TextControl
            label={__('URL', 'planet4-blocks-backend')}
            placeholder={__('Enter URL', 'planet4-blocks-backend')}
            value={this.props.social_media_url}
            onChange={this.props.onSocialMediaUrlChange}
          />
        </div>

        <div>
          <SelectControl
            label={__('Alignment', 'planet4-blocks-backend')}
            value={this.props.alignment_class}
            options={[
              {label: 'None', value: ''},
              {label: 'Left', value: 'alignleft'},
              {label: 'Center', value: 'aligncenter'},
              {label: 'Right', value: 'alignright'},
            ]}
            onChange={this.props.onAlignmentChange}
          />
        </div>

      </Fragment>
    );
  }

  render() {
    return (
      <div>
        {
          this.props.isSelected
            ? this.renderEdit()
            : null
        }
        <Preview showBar={this.props.isSelected}>
            <ServerSideRender
              block={'planet4-blocks/social-media'}
              attributes={{
                title: this.props.title,
                description: this.props.description,
                embed_type: this.props.embed_type,
                facebook_page_tab: this.props.facebook_page_tab,
                social_media_url: this.props.social_media_url,
                alignment_class: this.props.alignment_class,
              }}>
            </ServerSideRender>
        </Preview>
      </div>
    );
  }
}
