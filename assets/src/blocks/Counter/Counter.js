import {Component, Fragment} from '@wordpress/element';
import {
  TextControl as BaseTextControl,
  TextareaControl as BaseTextareaControl,
  ServerSideRender } from '@wordpress/components';
import { LayoutSelector } from '../../components/LayoutSelector/LayoutSelector';
import { Preview } from '../../components/Preview';
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';
import {URLInput} from "../../components/URLInput/URLInput";

const TextControl = withCharacterCounter( BaseTextControl );
const TextareaControl = withCharacterCounter( BaseTextareaControl );

export class Counter extends Component {
  constructor(props) {
    super(props);
  }

  renderEdit() {
    const { __ } = wp.i18n;
    return (
      <div>
        <div>
          <TextControl
            label= { __('Title', 'p4ge') }
            placeholder= { __('Enter title', 'p4ge') }
            value={ this.props.title }
            onChange={ this.props.onTitleChange }
            characterLimit={60}
          />
        </div>

        <div>
          <TextareaControl
            label= { __('Description', 'p4ge') }
            placeholder= { __('Enter description', 'p4ge') }
            value={ this.props.description }
            onChange={ this.props.onDescriptionChange }
            characterLimit={400}
          />
        </div>

        <div>
          <TextControl
            label= { __('Completed', 'p4ge') }
            placeholder= { __('e.g. number of signatures', 'p4ge') }
            type="number"
            value={ this.props.completed }
            onChange={ this.props.onCompletedChange }
          />
        </div>

        <div>
          <URLInput
            label={ __('Completed API URL', 'p4ge') }
            placeholder={ __('API URL of completed number. If filled in will overide the \'Completed\' field', 'p4ge') }
            value={ this.props.completed_api }
            onChange={ this.props.onCompletedAPIChange }
          />
        </div>

        <div>
          <TextControl
            label= { __('Target', 'p4ge') }
            placeholder= { __('e.g. target no. of signatures', 'p4ge') }
            type="number"
            value={ this.props.target }
            onChange={ this.props.onTargetChange }
          />
        </div>

        <div>
          <TextareaControl
            label= { __('Text', 'p4ge') }
            placeholder= { __('e.g. "signatures collected of %target%"', 'p4ge') }
            value={ this.props.text }
            onChange={ this.props.onTextChange }
          />
          <p className='FieldHelp'>These placeholders can be used: <code>%completed%</code>, <code>%target%</code>, <code>%remaining%</code> </p>
        </div>

      </div>
    );
  }

  render() {
    let style = 'plain';
    if (this.props.className) {
      style = this.props.className.split('is-style-')[1];
    }
    return (
      <div>
        {
          this.props.isSelected
            ? this.renderEdit()
            : null
        }
        <Preview showBar={ this.props.isSelected }>
          <ServerSideRender
            block={ 'planet4-blocks/counter' }
            attributes={{
              title: this.props.title,
              description: this.props.description,
              completed: this.props.completed,
              completed_api: this.props.completed_api,
              target: this.props.target,
              text: this.props.text,
              style
            }}>
          </ServerSideRender>
        </Preview>
      </div>
    );
  }
};
