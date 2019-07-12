import {React, Component} from 'react';
import {
  CheckboxControl,
  SelectControl,
  TextControl,
  ServerSideRender
} from '@wordpress/components';
import {LayoutSelector} from '../../components/LayoutSelector/LayoutSelector';
import {Preview} from '../../components/Preview';

export class Submenu extends Component {
  constructor(props) {
    super(props);
    this.state = {
      tagTokens: [],
      postTypeTokens: []
    };
  }

  renderEdit() {
    const {__} = wp.i18n;

    return (

      <div>
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

        <div>
          <br/>
          <div>Level 1</div>
          <SelectControl
            label="Submenu item"
            help="Submenu item"
            value={this.props.heading1}
            options={[
              {label: 'None', value: '0'},
              {label: 'Heading 1', value: '1'},
              {label: 'Heading 2', value: '2'},
            ]}
            onChange={this.props.onHeadingChange}
            className='block-attribute-wrapper'
          />

          <CheckboxControl
            label="Link"
            help="Submenu item"
            value={this.props.link1}
            checked={this.props.link1}
            onChange={this.props.onRowsChange}
            className="block-attribute-wrapper"
          />

          <SelectControl
            label="List style"
            help="List style"
            value={this.props.style1}
            options={[
              {label: 'None', value: 'none'},
              {label: 'Bullet', value: 'bullet'},
              {label: 'Number', value: 'number'},
            ]}
            onChange={this.props.onRowsChange}
            className='block-attribute-wrapper'
          />
        </div>

        <div>
          <div>Level 2</div>
          <SelectControl
            label="Submenu item"
            help="Submenu item"
            value={this.props.heading2}
            options={[
              {label: 'None', value: '0'},
              {label: 'Heading 1', value: '1'},
              {label: 'Heading 2', value: '2'},
            ]}
            onChange={this.props.onRowsChange}
            class="sdaf"
            className='block-attribute-wrapper'
          />

          <CheckboxControl
            label="Link"
            help="Submenu item"
            value={this.props.link1}
            checked={this.props.link1}
            onChange={this.props.onRowsChange}
            className="block-attribute-wrapper"
          />

          <SelectControl
            label="List style"
            help="List style"
            value={this.props.style2}
            options={[
              {label: 'None', value: 'none'},
              {label: 'Bullet', value: 'bullet'},
              {label: 'Number', value: 'number'},
            ]}
            onChange={this.props.onRowsChange}
            className='block-attribute-wrapper'
          />
        </div>

        {/*{*/}
        {/*	this.props.submenu_style === 3 &&*/}
        {/*	(this.props.tags.length === 0 || this.props.post_types.length === 0)*/}
        {/*	? <div>*/}
        {/*			<label>Manual override</label>*/}
        {/*			<FormTokenField*/}
        {/*				value={ this.props.selectedPosts }*/}
        {/*				suggestions={ postsSuggestions }*/}
        {/*				label='CAUTION: Adding covers manually will override the automatic functionality.*/}
        {/*				DRAG & DROP: Drag and drop to reorder cover display priority.'*/}
        {/*				onChange={ tokens => this.props.onSelectedPostsChange(tokens) }*/}
        {/*				placeholder="Select Tags"*/}
        {/*			/>*/}
        {/*		</div>*/}
        {/*	: null*/}
        {/*}*/}

      </div>
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
        {/*<Preview showBar={ this.props.isSelected }>*/}
        {/*	<ServerSideRender*/}
        {/*		block={ 'planet4-blocks/submenu' }*/}
        {/*		attributes={{*/}
        {/*			submenu_style: this.props.submenu_style,*/}
        {/*			title: this.props.title,*/}
        {/*		}}>*/}
        {/*	</ServerSideRender>*/}
        {/*</Preview>*/}
      </div>
    );
  }
};
