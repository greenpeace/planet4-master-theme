import { React, Component } from 'react';
import {
  TextControl,
  TextareaControl,
  ServerSideRender } from '@wordpress/components';
import { LayoutSelector } from '../../components/LayoutSelector/LayoutSelector';
import { Preview } from '../../components/Preview';

export class Counter extends Component {
  constructor(props) {
    super(props);
  }

  renderEdit() {
    const { __ } = wp.i18n;

    return (
      <div>
        <h3>{ __('What style of counter do you need?', 'p4ge') }</h3>

        <div>
          <LayoutSelector
            selectedOption={ this.props.style }
            onSelectedLayoutChange={ this.props.onSelectedLayoutChange }
            options={[
              {
                label: __('Text Only', 'p4ge'),
                image: window.p4ge_vars.home + 'images/counter_th_text.png',
                value: 'plain',
                help: __('Text to describe your progress', 'p4ge')
              },
              {
                label: __('Progress Bar', 'p4ge'),
                image: window.p4ge_vars.home + 'images/counter_th_bar.png',
                value: 'bar',
                help: __('A bar to visualise the progress.', 'p4ge'),
              },
              {
                label: __('Progress Dial', 'p4ge'),
                image: window.p4ge_vars.home + 'images/counter_th_arc.png',
                value: 'arc',
                help: __('A dial to visualise the progress.', 'p4ge')
              },
              {
                label: __('Progress bar inside EN Form', 'p4ge'),
                image: window.p4ge_vars.home + 'images/counter_th_bar.png',
                value: 'en-forms-bar',
                help: __('A bar inside an En Form. Select this only if you are adding an EN Form to the same page.', 'p4ge')
              },
            ]}
          />
        </div>

        <div>
          <TextControl
            label= { __('Title', 'p4ge') }
            placeholder= { __('Enter title', 'p4ge') }
            value={ this.props.title }
            onChange={ this.props.onTitleChange }
          />
        </div>

        <div>
          <TextareaControl
            label= { __('Description', 'p4ge') }
            placeholder= { __('Enter description', 'p4ge') }
            value={ this.props.description }
            onChange={ this.props.onDescriptionChange }
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
          <TextControl
            label= { __('Completed API URL', 'p4ge') }
            placeholder= { __('API URL of completed number. If filled in will overide the \'Completed\' field', 'p4ge') }
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
              style: this.props.style,
              completed: this.props.completed,
              completed_api: this.props.completed_api,
              target: this.props.target,
              text: this.props.text,
            }}>
          </ServerSideRender>
        </Preview>
      </div>
    );
  }
};