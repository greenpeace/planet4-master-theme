import {React, Component, Fragment} from 'react';
import {Preview} from '../../components/Preview';
import {LayoutSelector} from '../../components/LayoutSelector/LayoutSelector';
import {
  TextControl,
  TextareaControl,
  ServerSideRender
} from '@wordpress/components';

export class ENForm extends Component {
  constructor(props) {
    super(props);
  }

  renderEdit() {
    const {__} = wp.i18n;

    return (
      <Fragment>
        <div>
          <h2>{__('EN Form options', 'p4gen')}</h2>
          <p><i>{__(
            'Display options for EN Forms',
            'p4gen'
          )}</i></p>

          <div>
            <LayoutSelector
              selectedOption={this.props.en_form_style}
              onSelectedLayoutChange={this.props.onSelectedLayoutChange}
              options={[
                {
                  label: __('Page body / text size width. No background.', 'p4gen'),
                  image: window.p4ge_vars.home + 'images/enfullwidth.jpg',
                  value: 1,
                  help: __('Use: on long pages (more than 5 screens) when list items are long (+ 10 words)<br>No max items<br>recommended.', 'p4gen'),
                }, {
                  label: __('Full page width. With background image.', 'p4gen'),
                  image: window.p4ge_vars.home + 'images/enfullwidthbg.jpg',
                  value: 2,
                  help: __('This form has a background image that expands the full width of the browser (aka "Happy Point").', 'p4gen'),
                },
                {
                  label: __('Form on the side.', 'p4gen'),
                  image: window.p4ge_vars.home + 'images/submenu-sidebar.jpg',
                  value: 3,
                  help: __('Form will be added to the top of the page, on the right side for most languages and on the left side for Right-to-left(RTL) languages.', 'p4gen'),
                },
              ]}
            />
          </div>

          <div>
            <TextControl
              label={ __( 'Form Title', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter title', 'planet4-engagingnetworks' ) }
              value={this.props.title}
              onChange={this.props.onTitleChange}
            />
          </div>

          <div>
            <TextControl
              label={ __( 'Form Description', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter description', 'planet4-engagingnetworks' ) }
              value={this.props.description}
              onChange={this.props.onDescriptionChange}
            />
          </div>

          <div>
            <TextControl
              label={ __( 'Content Title', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter content title', 'planet4-engagingnetworks' ) }
              value={this.props.content_title}
              onChange={this.props.onContentTitleChange}
            />
          </div>

          <div>
            <TextareaControl
              label={ __( 'Content Description', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter content description', 'planet4-engagingnetworks' ) }
              value={this.props.cnotent_description}
              onChange={this.props.onContentDescriptionChange}
            />
          </div>

          <div>
            <TextControl
              label={ __( 'Call to Action button (e.g. "Sign up now!")', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter the "Call to Action" button text', 'planet4-engagingnetworks' ) }
              value={this.props.button_text}
              onChange={this.props.onCTAButtonTextChange}
            />
          </div>

          <div>
            <TextareaControl
              label={ __( 'Text below Call to Action button', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter text to go below the button', 'planet4-engagingnetworks' ) }
              value={this.props.text_below_button}
              onChange={this.props.onCTAButtonTextChange}
            />
          </div>

          <div>
            <TextControl
              label={ __( '"Thank you" main text / Title (e.g. "Thank you for signing!")', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter "Thank you" main text / Title', 'planet4-engagingnetworks' ) }
              value={this.props.thankyou_title}
              onChange={this.props.onMainThankYouTextChange}
            />
          </div>

          <div>
            <TextControl
              label={ __( '"Thank You" secondary message / Subtitle (e.g. "Your support means world")', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter Thank you Subtitle', 'planet4-engagingnetworks' ) }
              value={this.props.thankyou_subtitle}
              onChange={this.props.onSecondaryThankYouMessageChange}
            />
          </div>

          <div>
            <TextControl
              label={ __( '"Thank You" social media message (e.g. "Can you share it with your family and friends?")', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter Donate Message', 'planet4-engagingnetworks' ) }
              value={this.props.thankyou_donate_message}
              onChange={this.props.onThankYouDonateMessageChange}
            />
          </div>

          <div>
            <TextControl
              label={ __( '"Thank You" donate message (e.g. "or make a donation")', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter Take Action Message', 'planet4-engagingnetworks' ) }
              value={this.props.thankyou_take_action_message}
              onChange={this.props.onThankYouTakeActionMessageChange}
            />
          </div>

          <div>
            <TextControl
              label={ __( '"Thank you page" url (Title and Subtitle will not be shown)', 'planet4-engagingnetworks' ) }
              placeholder={ __( 'Enter "Thank you page" url', 'planet4-engagingnetworks' ) }
              value={this.props.thankyou_url}
              onChange={this.props.onThankYouTakeActionMessageChange}
            />
          </div>

{
  /*

			$goal_options = [
				0                 => __( '- Select Goal -', 'planet4-engagingnetworks' ),
				'Petition Signup' => 'Petition Signup',
				'Action Alert'    => 'Action Alert',
				'Contact Form'    => 'Contact Form',
				'Other'           => 'Other',
			];

			$fields = [
				[
					'label'       => __( 'Engaging Network Live Pages', 'planet4-engagingnetworks' ),
					'description' => $pages ? __( 'Select the Live EN page that this form will be submitted to.', 'planet4-engagingnetworks' ) : __( 'Check your EngagingNetworks settings!', 'planet4-engagingnetworks' ),
					'attr'        => 'en_page_id',
					'type'        => 'select',
					'meta'        => [
						'required' => '',
					],
					'options'     => $pages_options,
				],
				[
					'label'       => __( 'Goal', 'planet4-engagingnetworks' ),
					'attr'        => 'enform_goal',
					'type'        => 'select',
					'meta'        => [
						'required' => '',
					],
					'options'     => $goal_options,
					'description' => __( 'When form data submitted to EN, The value added in "Goal" field is used in the GTM dataLayer push event.', 'planet4-engagingnetworks' ),
				],
				[
					'attr'    => 'en_form_style',
					'label'   => __( 'What style of form do you need?', 'planet4-engagingnetworks' ),
					'type'    => 'p4en_radio',
					'options' => [
						[
							'value' => 'full-width',
							'label' => __( 'Page body / text size width. No background.', 'planet4-engagingnetworks' ),
							'desc'  => __( 'Best to use inside pages. Form width will align with body / text width.', 'planet4-engagingnetworks' ),
							'image' => esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enfullwidth.png' ),
						],
						[
							'value' => 'full-width-bg',
							'label' => __( 'Full page width. With background image.', 'planet4-engagingnetworks' ),
							'desc'  => __( 'This form has a background image that expands the full width of the browser (aka "Happy Point").', 'planet4-engagingnetworks' ),
							'image' => esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enfullwidthbg.png' ),
						],
						[
							'value' => 'side-style',
							'label' => __( 'Form on the side.', 'planet4-engagingnetworks' ),
							'desc'  => __( 'Form will be added to the top of the page, on the right side for most languages and on the left side for Right-to-left(RTL) languages.', 'planet4-engagingnetworks' ),
							'image' => esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/submenu-sidebar.jpg' ),
						],
					],
				],
				[
					'label'       => __( 'Background image for full width / on the side forms styles', 'planet4-engagingnetworks' ),
					'attr'        => 'background',
					'type'        => 'attachment',
					'libraryType' => [ 'image' ],
					'addButton'   => __( 'Select Background Image', 'planet4-engagingnetworks' ),
					'frameTitle'  => __( 'Select Background Image', 'planet4-engagingnetworks' ),
				],
				[
					'label'       => __( 'Planet 4 Engaging Networks form', 'planet4-engagingnetworks' ),
					'description' => $forms ? __( 'Select the P4EN Form that will be displayed.', 'planet4-engagingnetworks' ) : __( 'Create an EN Form', 'planet4-engagingnetworks' ),
					'attr'        => 'en_form_id',
					'type'        => 'select',
					'meta'        => [
						'required' => '',
					],
					'options'     => $forms_options,
				],
			];
  */
}

          <hr/>

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
            block={'planet4-engagingnetworks/enform'}
            attributes={{
              title: this.props.title,
              description: this.props.description,
              content_title: this.props.content_title,
              cnotent_description: this.props.cnotent_description,
              button_text: this.props.button_text,
              thankyou_title: this.props.thankyou_title,
              thankyou_subtitle: this.props.thankyou_subtitle,
              thankyou_donate_message: this.props.thankyou_donate_message,
              thankyou_take_action_message: this.props.thankyou_take_action_message,
              thankyou_url: this.props.thankyou_url,
            }}
          >
          </ServerSideRender>
        </Preview>
      </div>
    );
  };
}
