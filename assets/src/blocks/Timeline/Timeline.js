import {RawHTML, Component, Fragment} from '@wordpress/element';
import {
  CheckboxControl,
  TextControl,
  TextareaControl,
  SelectControl,
  ServerSideRender
} from '@wordpress/components';
import {Preview} from '../../components/Preview';

export class Timeline extends Component {
  constructor(props) {
    super(props);
  }

  componentDidMount() {
    this.loadTimeline(this.props);
  }

  componentDidUpdate(prevProps) {
    this.loadTimeline(prevProps);
  }

  loadTimeline(data) {
    const timelinejs_version = '3.6.3';

    let js = 'https://cdn.knightlab.com/libs/timeline3/' + timelinejs_version + '/js/timeline-min.js';
    let scriptLoaded = this.loadScriptAsync(js);
    scriptLoaded.then(function () {
      new TL.Timeline('timeline-1', data.google_sheets_url, {
        "timenav_position": data.timenav_position,
        "start_at_end": data.start_at_end,
        "language": data.language
      });
    }.bind(this));
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

    const position = [
      {label: 'Bottom', value: 'bottom'},
      {label: 'Top', value: 'top'},
    ]

    const languages = [
      {label: 'Afrikaans', value: 'af'},
      {label: 'Arabic', value: 'ar'},
      {label: 'Armenian', value: 'hy'},
      {label: 'Basque', value: 'eu'},
      {label: 'Belarusian', value: 'be'},
      {label: 'Bulgarian', value: 'bg'},
      {label: 'Catalan', value: 'ca'},
      {label: 'Chinese', value: 'zh-cn'},
      {label: 'Croatian / Hrvatski', value: 'hr'},
      {label: 'Czech', value: 'cz'},
      {label: 'Danish', value: 'da'},
      {label: 'Dutch', value: 'nl'},
      {label: 'English', value: 'en'},
      {label: 'English (24-hour time)', value: 'en-24hr'},
      {label: 'Esperanto', value: 'eo'},
      {label: 'Estonian', value: 'et'},
      {label: 'Faroese', value: 'fo'},
      {label: 'Farsi', value: 'fa'},
      {label: 'Finnish', value: 'fi'},
      {label: 'French', value: 'fr'},
      {label: 'Frisian', value: 'fy'},
      {label: 'Galician', value: 'gl'},
      {label: 'Georgian', value: 'ka'},
      {label: 'German / Deutsch', value: 'de'},
      {label: 'Greek', value: 'el'},
      {label: 'Hebrew', value: 'he'},
      {label: 'Hindi', value: 'hi'},
      {label: 'Hungarian', value: 'hu'},
      {label: 'Icelandic', value: 'is'},
      {label: 'Indonesian', value: 'id'},
      {label: 'Irish', value: 'ga'},
      {label: 'Italian', value: 'it'},
      {label: 'Japanese', value: 'ja'},
      {label: 'Korean', value: 'ko'},
      {label: 'Latvian', value: 'lv'},
      {label: 'Lithuanian', value: 'lt'},
      {label: 'Luxembourgish', value: 'lb'},
      {label: 'Malay', value: 'ms'},
      {label: 'Myanmar', value: 'my'},
      {label: 'Nepali', value: 'ne'},
      {label: 'Norwegian', value: 'no'},
      {label: 'Polish', value: 'pl'},
      {label: 'Portuguese', value: 'pt'},
      {label: 'pt-br', value: 'Portuguese (Brazilian)'},
      {label: 'Romanian', value: 'ro'},
      {label: 'Romansh', value: 'rm'},
      {label: 'Russian', value: 'ru'},
      {label: 'Serbian - Cyrillic', value: 'sr-cy'},
      {label: 'Serbian - Latin', value: 'sr'},
      {label: 'Sinhalese', value: 'si'},
      {label: 'Slovak', value: 'sk'},
      {label: 'Slovenian', value: 'sl'},
      {label: 'Spanish', value: 'es'},
      {label: 'Swedish', value: 'sv'},
      {label: 'Tagalog', value: 'tl'},
      {label: 'Tamil', value: 'ta'},
      {label: 'Taiwanese', value: 'zh-tw'},
      {label: 'Telugu', value: 'te'},
      {label: 'Thai', value: 'th'},
      {label: 'Turkish', value: 'tr'},
      {label: 'Ukrainian', value: 'uk'},
      {label: 'Urdu', value: 'ur'},
    ]

    let url_desc  = __(
      'Enter the URL of the Google Sheets spreadsheet containing your timeline data.',
      'p4ge'
    );
    url_desc += '<br><a href="https://timeline.knightlab.com/#make" target="_blank" rel="noopener noreferrer">';
    url_desc += __(
      'See the TimelineJS website for a template GSheet.',
      'p4ge'
    );
    url_desc += '</a><br>';
    url_desc += __(
      'Copy this, add your own timeline data, and publish to the web.',
      'p4ge'
    );
    url_desc += '<br>';
    url_desc += __(
      "Once you have done so, use the URL from your address bar (not the one provided in Google's 'publish to web' dialog).",
      'p4ge'
    );

    return (
      <Fragment>
        <div>
          <h2>{__('Timeline options', 'p4ge')}</h2>
          <p><i>{__(
            'Display a timeline from a Google Sheet',
            'p4ge'
          )}</i></p>

          <div>
            <TextControl
              label={__('Timeline Title', 'p4ge')}
              placeholder={__('Enter title', 'p4ge')}
              value={this.props.timeline_title}
              onChange={this.props.onTimelineTitleChange}
            />
          </div>

          <div>
            <TextareaControl
              label={__('Description', 'p4ge')}
              placeholder={__('Enter description', 'p4ge')}
              value={this.props.description}
              onChange={this.props.onDescriptionChange}
            />
          </div>

          <div>
            <TextControl
              label={__('Google Sheets URL', 'p4ge')}
              placeholder={__('Enter URL', 'p4ge')}
              help=<RawHTML>{url_desc}</RawHTML>
              value={this.props.google_sheets_url}
              onChange={this.props.onGoogleSheetsUrlChange}
            />
          </div>

          <div>
            <SelectControl
              label={__('Language', 'p4ge')}
              value={this.props.language}
              options={languages}
              onChange={(e) => this.props.onLanguageChange(e)}
            />
          </div>

          <div>
            <SelectControl
              label={__('Timeline navigation position', 'p4ge')}
              value={this.props.timenav_position}
              options={position}
              onChange={(e) => this.props.onTimenavPositionChange(e)}
            />
          </div>

          <div>
            <CheckboxControl
              label={__('Start at end', 'p4ge')}
              help={__('Begin at the end of the timeline', 'p4ge')}
              value={this.props.start_at_end}
              checked={this.props.start_at_end}
              onChange={(e) => this.props.onStartAtEndChange(e)}
            />
          </div>

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
            block={'planet4-blocks/timeline'}
            attributes={{
              timeline_title: this.props.timeline_title,
              description: this.props.description,
              google_sheets_url: this.props.google_sheets_url,
              language: this.props.language,
              timenav_position: this.props.timenav_position,
              start_at_end: this.props.start_at_end,
            }}
            urlQueryArgs={{post_id: document.querySelector('#post_ID').value}}
          >
          </ServerSideRender>
        </Preview>
      </div>
    );
  };
}
