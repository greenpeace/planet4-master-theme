import {React, Component, Fragment} from 'react';
import {
  Button,
  TextControl,
  ServerSideRender
} from '@wordpress/components';
import {LayoutSelector} from '../../components/LayoutSelector/LayoutSelector';
import {Preview} from '../../components/Preview';
import {MenuLevel} from "./MenuLevel";

export class Submenu extends Component {
  constructor(props) {
    super(props);
  }

  renderEdit() {
    const {__} = wp.i18n;

    return (

      <div>
        <h2>{__('Anchor Link Submenu', 'p4ge')}</h2>
        <p><i>{__(
          'An in-page table of contents to help users have a sense of what\'s on the page and let them jump to a topic they are interested in.',
          'p4ge'
        )}</i></p>
        <h3>{__('What style of menu do you need?', 'p4ge')}</h3>

        <div>
          <LayoutSelector
            selectedOption={this.props.submenu_style}
            onSelectedLayoutChange={this.props.onSelectedLayoutChange}
            options={[
              {
                label: __('Long full-width', 'p4ge'),
                image: window.p4ge_vars.home + 'images/submenu-long.jpg',
                value: 1,
                help: __('Use: on long pages (more than 5 screens) when list items are long (+ 10 words)<br>No max items<br>recommended.')
              }, {
                label: __('Short full-width', 'p4ge'),
                image: window.p4ge_vars.home + 'images/submenu-short.jpg',
                value: 2,
                help: __('Use: on long pages (more than 5 screens) when list items are short (up to 5 words)<br>No max items<br>recommended.'),
              },
              {
                label: __('Short sidebar', 'p4ge'),
                image: window.p4ge_vars.home + 'images/submenu-sidebar.jpg',
                value: 3,
                help: __('Use: on long pages (more than 5 screens) when list items are short (up to 10 words)<br>Max items<br>recommended: 9')
              },
            ]}
          />
        </div>

        <div>
          <TextControl
            label="Submenu Title"
            placeholder="Enter title"
            value={this.props.title}
            onChange={this.props.onTitleChange}
          />
        </div>

        <hr/>
        {this.props.levels.map((heading, i) => {
          return (
            <MenuLevel
              {...heading}
              onHeadingChange={this.props.onHeadingChange}
              onLinkChange={this.props.onLinkChange}
              onStyleChange={this.props.onStyleChange}
              index={i}
              key={i}
            />
          );
        })}

        <div>
          <Button isPrimary
                  onClick={this.props.addLevel}
                  disabled={this.props.levels.length >= 3 || this.props.levels.slice(-1)[0].heading === 0}
          >
            Add level
          </Button>
          <Button isDefault
                  onClick={this.props.removeLevel} disabled={this.props.levels.length <= 1}
          >
            Remove level
          </Button>
        </div>
      </div>
    );
  }

  render() {
    return (
      <Fragment>
        {
          this.props.isSelected
            ? this.renderEdit()
            : null
        }
        <Preview showBar={this.props.isSelected}>
          <ServerSideRender
            block={'planet4-blocks/submenu'}
            attributes={{
              submenu_style: this.props.submenu_style,
              title: this.props.title,
              levels: this.props.levels,
            }}
            urlQueryArgs={{post_id: document.querySelector('#post_ID').value}}
          >
          </ServerSideRender>
        </Preview>
      </Fragment>
    );
  };
}
