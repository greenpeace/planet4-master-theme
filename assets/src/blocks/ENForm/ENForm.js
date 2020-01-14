import {Component} from '@wordpress/element';
import {Preview} from '../../components/Preview';
import {LayoutSelector} from '../../components/LayoutSelector/LayoutSelector';
import {FormSectionTitle} from '../../components/FormSectionTitle/FormSectionTitle';
import {FormHelp} from '../../components/FormHelp/FormHelp';
import {InlineFormFeedback} from '../../components/InlineFormFeedback/InlineFormFeedback';
import {
  TextControl,
  TextareaControl,
  ToggleControl,
  SelectControl,
  ServerSideRender
} from '@wordpress/components';
import {MediaPlaceholder} from "@wordpress/editor";
import {ValidationMessage} from "../../components/ValidationMessage/ValidationMessage";

const {__} = wp.i18n;

export class ENForm extends Component {
  constructor(props) {
    super(props);
  }

  renderEdit() {
    const {getCurrentPostType} = wp.data.select("core/editor");
    const currentPostType      = getCurrentPostType();

    let flattenedPages = [];
    let pagesByType;

    for (var i in window.p4en_vars.pages) {
      pagesByType = window.p4en_vars.pages[i].map(page => {
        return { label: page.name, value: page.id };
      });
      flattenedPages = flattenedPages.concat(
        { label: '-- ' + i, value: i }, // Page type label
        ...pagesByType
      );
    }

    const en_forms = window.p4en_vars.forms.map(form => {
      return { label: form.post_title, value: form.ID };
    });

    return (
      <div>
        <div>
          <FormSectionTitle>{__(
            'EN Form options',
            'planet4-engagingnetworks-backend'
          )}</FormSectionTitle>

          <FormHelp>{__(
            'Display options for EN Forms',
            'planet4-engagingnetworks-backend'
          )}</FormHelp>

          <SelectControl
            label={__( 'Engaging Network Live Pages', 'planet4-engagingnetworks-backend' )}
            value={this.props.en_page_id}
            options={[
              { label: 'No pages', value: 0 },
              ...flattenedPages
            ]}
            disabled={!flattenedPages.length}
            onChange={this.props.onPageChange}
          />

          { flattenedPages.length
            ? <FormHelp>
                { __( 'Select the Live EN page that this form will be submitted to.', 'planet4-engagingnetworks-backend' ) }
              </FormHelp>
            : <InlineFormFeedback>
                { __( 'Check your EngagingNetworks settings!', 'planet4-engagingnetworks-backend' ) }
              </InlineFormFeedback>
          }

          <SelectControl
            label={__( '- Select Goal -', 'planet4-engagingnetworks-backend' )}
            value={this.props.enform_goal}
            options={[
              { label: 'Petition Signup', value: 'Petition Signup' },
              { label: 'Action Alert', value: 'Action Alert' },
              { label: 'Contact Form', value: 'Contact Form' },
              { label: 'Other', value: 'Other' },
            ]}
            onChange={this.props.onGoalChange}
          />

          <div>
            <LayoutSelector
              selectedOption={this.props.en_form_style}
              onSelectedLayoutChange={this.props.onSelectedLayoutChange}
              options={[
                {
                  label: __( 'Page body / text size width. No background.', 'planet4-engagingnetworks-backend' ),
                  image: window.p4en_vars.home + 'images/enfullwidth.png',
                  value: 'full-width',
                  help: __( 'Use: on long pages (more than 5 screens) when list items are long (+ 10 words)<br>No max items<br>recommended.', 'planet4-engagingnetworks-backend' ),
                }, {
                  label: __( 'Full page width. With background image.', 'planet4-engagingnetworks-backend' ),
                  image: window.p4en_vars.home + 'images/enfullwidthbg.png',
                  value: 'full-width-bg',
                  help: __( 'This form has a background image that expands the full width of the browser (aka "Happy Point").', 'planet4-engagingnetworks-backend' ),
                },
                {
                  label: __( 'Form on the side.', 'planet4-engagingnetworks-backend' ),
                  image: window.p4en_vars.home + 'images/submenu-sidebar.jpg',
                  value: 'side-style',
                  help: __( 'Form will be added to the top of the page, on the right side for most languages and on the left side for Right-to-left(RTL) languages.', 'planet4-engagingnetworks-backend' ),
                },
              ]}
            />
          </div>

          <div>
            <TextControl
              label={ __( 'Form Title', 'planet4-engagingnetworks-backend' ) }
              placeholder={ __( 'Enter title', 'planet4-engagingnetworks-backend' ) }
              value={this.props.title}
              onChange={this.props.onTitleChange}
            />
          </div>

          <div>
            <TextareaControl
              label={ __( 'Form Description', 'planet4-engagingnetworks-backend' ) }
              placeholder={ __( 'Enter description', 'planet4-engagingnetworks-backend' ) }
              value={this.props.description}
              onChange={this.props.onDescriptionChange}
            />
          </div>

          { "side-style" === this.props.en_form_style &&
          (<div>

              { "campaign" === currentPostType && (
                <div>
                  <ToggleControl
                    label={__( 'Use Campaign Logo?', 'planet4-engagingnetworks-backend' )}
                    value={this.props.campaign_logo}
                    checked={this.props.campaign_logo}
                    onChange={this.props.onCampaignLogoChange}
                  />
                </div>
              )}

              <div>
                <TextControl
                  label={ __( 'Content Title', 'planet4-engagingnetworks-backend' ) }
                  placeholder={ __( 'Enter content title', 'planet4-engagingnetworks-backend' ) }
                  value={this.props.content_title}
                  onChange={this.props.onContentTitleChange}
                />
              </div>

              <div>
                <SelectControl
                  label={ __( 'Content Title text size', 'planet4-engagingnetworks-backend' ) }
                  value={this.props.content_title_size}
                  options={ [
                    { label: __( 'Select title size', 'planet4-engagingnetworks-backend' ), value: '' },
                    { label: 'h1', value: 'h1' },
                    { label: 'h2', value: 'h2' },
                    { label: 'h3', value: 'h3' },
                  ] }
                  onChange={this.props.onContentTitleSizeChange}
                />
              </div>

              <div>
                <TextareaControl
                  label={ __( 'Content Description', 'planet4-engagingnetworks-backend' ) }
                  placeholder={ __( 'Enter content description', 'planet4-engagingnetworks-backend' ) }
                  value={this.props.content_description}
                  onChange={this.props.onContentDescriptionChange}
                />
              </div>
            </div>)
          }

          <div>
            <TextControl
              label={ __( 'Call to Action button (e.g. "Sign up now!")', 'planet4-engagingnetworks-backend' ) }
              placeholder={ __( 'Enter the "Call to Action" button text', 'planet4-engagingnetworks-backend' ) }
              value={this.props.button_text}
              onChange={this.props.onCTAButtonTextChange}
            />
          </div>

          <div>
            <TextareaControl
              label={ __( 'Text below Call to Action button', 'planet4-engagingnetworks-backend' ) }
              placeholder={ __( 'Enter text to go below the button', 'planet4-engagingnetworks-backend' ) }
              value={this.props.text_below_button}
              onChange={this.props.onCTATextBelowButtonChange}
            />
          </div>

          <FormSectionTitle>{__(
            '"Thank You" message settings',
            'planet4-engagingnetworks-backend'
          )}</FormSectionTitle>

          <div>
            <TextControl
              label={ __( 'Main text / Title', 'planet4-engagingnetworks-backend' ) }
              placeholder={ __( 'e.g. "Thank you for signing!"', 'planet4-engagingnetworks-backend' ) }
              value={this.props.thankyou_title}
              onChange={this.props.onMainThankYouTextChange}
            />
          </div>

          <div>
            <TextControl
              label={ __( 'Secondary message / Subtitle', 'planet4-engagingnetworks-backend' ) }
              placeholder={ __( 'e.g. "Your support means world"', 'planet4-engagingnetworks-backend' ) }
              value={this.props.thankyou_subtitle}
              onChange={this.props.onSecondaryThankYouMessageChange}
            />
          </div>

          <div>
            <TextControl
              label={ __( 'Social media message', 'planet4-engagingnetworks-backend' ) }
              placeholder={ __( 'e.g. "Can you share it with your family and friends?"', 'planet4-engagingnetworks-backend' ) }
              value={this.props.thankyou_social_media_message}
              onChange={this.props.onThankYouTakeActionMessageChange}
            />
          </div>

          <br/>

          <ToggleControl
            label={__( 'Hide "DONATE" button in Thank You message', 'planet4-engagingnetworks-backend' )}
            value={this.props.donate_button_checkbox}
            checked={this.props.donate_button_checkbox}
            onChange={this.props.onDonateButtonCheckboxChange}
            className="hide-donate-toggle-field"
          />
          { true !== this.props.donate_button_checkbox && (
            <div>
              <TextControl
                label={ __( 'Donate message', 'planet4-engagingnetworks-backend' ) }
                placeholder={ __( 'e.g. "or make a donation"', 'planet4-engagingnetworks-backend' ) }
                value={this.props.thankyou_donate_message}
                onChange={this.props.onThankYouDonateMessageChange}
              />
              <TextControl
                label={ __( 'Custom DONATE url', 'planet4-engagingnetworks-backend' ) }
                placeholder={ __( 'If empty, the default "DONATE" P4 Button link will be used', 'planet4-engagingnetworks-backend' ) }
                value={this.props.custom_donate_url}
                onChange={this.props.onCustomDonateUrlChange}
              />
            </div> )
          }
          <br></br>
          <div>
            <TextControl
              label={ __( 'Thank you page URL (Title, Subtitle, Social media message / icons and DONATE will not be shown)', 'planet4-engagingnetworks-backend' ) }
              placeholder={ __( 'Enter "Thank you page" url', 'planet4-engagingnetworks-backend' ) }
              value={this.props.thankyou_url}
              onChange={this.props.onThankYouURLChange}
            />
          </div>

          { "full-width" !== this.props.en_form_style &&
            <div>
              <MediaPlaceholder
                labels={{ title: __( 'Background', 'planet4-engagingnetworks-backend' ), instructions: __( 'Select an image.', 'planet4-engagingnetworks-backend' )}}
                icon="format-image"
                onSelect={ this.props.onSelectImage }
                onError={this.props.onUploadError}
                accept="image/*"
                allowedTypes={["image"]}
              />
            </div>
          }

          <div>
            <SelectControl
              label={__( 'Planet 4 Engaging Networks form', 'planet4-engagingnetworks-backend' )}
              value={this.props.en_form_id}
              options={[
                { label: 'No forms', value: 0 },
                ...en_forms
              ]}
              onChange={this.props.onFormChange}
            />
            <FormHelp>{ this.props.forms
              ? __( 'Select the P4EN Form that will be displayed.', 'planet4-engagingnetworks-backend' )
              : __( 'Create an EN Form', 'planet4-engagingnetworks-backend' )
            }</FormHelp>
          </div>
        </div>
      </div>
    );
  }

  render() {
    let validationMessage = [];

    if ( false === this.props.isSelected ) {
      if ( undefined === this.props.en_page_id || 0 === this.props.en_page_id ) {
        validationMessage.push( __( '"Engaging Network Live Pages" field is required!', 'planet4-engagingnetworks-backend' ));
      }
      if ( undefined === this.props.button_text || '' === this.props.button_text ) {
        validationMessage.push( __( '"Call to Action button" field is required!', 'planet4-engagingnetworks-backend' ));
      }
      if ( undefined === this.props.en_form_id || 0 === this.props.en_form_id ) {
        validationMessage.push( __( '"Planet 4 Engaging Networks form" field is required!', 'planet4-engagingnetworks-backend' ));
      }
    }

    return (
      <div>
        {
          this.props.isSelected
            ? this.renderEdit()
            : null
        }
        <Preview showBar={this.props.isSelected} isSelected={this.props.isSelected}>

          { validationMessage.length ?
              <ValidationMessage
                message={validationMessage}
              />
            :
              <ServerSideRender
                block={'planet4-blocks/enform'}
                attributes={{
                  en_page_id: this.props.en_page_id,
                  en_form_id: this.props.en_form_id,
                  en_form_style: this.props.en_form_style,
                  title: this.props.title,
                  description: this.props.description,
                  campaign_logo: this.props.campaign_logo,
                  content_title: this.props.content_title,
                  content_title_size: this.props.content_title_size,
                  content_description: this.props.content_description,
                  button_text: this.props.button_text,
                  thankyou_title: this.props.thankyou_title,
                  thankyou_subtitle: this.props.thankyou_subtitle,
                  thankyou_donate_message: this.props.thankyou_donate_message,
                  thankyou_social_media_message: this.props.thankyou_social_media_message,
                  donate_button_checkbox: this.props.donate_button_checkbox,
                  thankyou_url: this.props.thankyou_url,
                  background: this.props.background,
                }}
              >
              </ServerSideRender>
          }
        </Preview>
      </div>
    );
  };
}
