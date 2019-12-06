import {Component, Fragment} from '@wordpress/element';
import {Preview} from '../../components/Preview';
import {
  TextControl,
  TextareaControl,
  ServerSideRender
} from '@wordpress/components';

export class Cookies extends Component {
  constructor(props) {
    super(props);
  }

  renderEdit() {
    const {__} = wp.i18n;

    return (
      <Fragment>
        <div>
          <h2>{__('Cookies options', 'p4ge')}</h2>
          <p><i>{__(
            'Display opt-in options for cookies',
            'p4ge'
          )}</i></p>

          <div>
            <TextControl
              label="Title"
              placeholder="Enter title"
              value={this.props.title}
              onChange={this.props.onTitleChange}
            />
          </div>

          <div>
            <TextareaControl
              label="Description"
              placeholder="Enter description"
              value={this.props.description}
              onChange={this.props.onDescriptionChange}
            />
          </div>

          <hr/>

          <div>
            <TextControl
              label="Necessary Cookies Name"
              placeholder="Enter cookies name"
              value={this.props.necessary_cookies_name}
              onChange={this.props.onNecessaryCookiesNameChange}
            />
          </div>

          <div>
            <TextareaControl
              label="Necessary Cookies Description"
              placeholder="Enter cookies description"
              value={this.props.necessary_cookies_description}
              onChange={this.props.onNecessaryCookiesDescriptionChange}
            />
          </div>

          <hr/>

          <div>
            <TextControl
              label="All Cookies Name"
              placeholder="Enter cookies name"
              value={this.props.all_cookies_name}
              onChange={this.props.onAllCookiesNameChange}
            />
          </div>

          <div>
            <TextareaControl
              label="All Cookies Description"
              placeholder="Enter cookies description"
              value={this.props.all_cookies_description}
              onChange={this.props.onAllCookiesDescriptionChange}
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
            block={'planet4-blocks/cookies'}
            attributes={{
              title: this.props.title,
              description: this.props.description,
              necessary_cookies_name: this.props.necessary_cookies_name,
              necessary_cookies_description: this.props.necessary_cookies_description,
              all_cookies_name: this.props.all_cookies_name,
              all_cookies_description: this.props.all_cookies_description,
            }}
            urlQueryArgs={{post_id: document.querySelector('#post_ID').value}}
          >
          </ServerSideRender>
        </Preview>
      </div>
    );
  };
}
