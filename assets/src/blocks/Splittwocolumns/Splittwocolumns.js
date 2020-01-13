import {Component,Fragment} from "@wordpress/element";
import {
  TextControl as BaseTextControl,
  TextareaControl as BaseTextareaControl,
  FocalPointPicker,
  ServerSideRender,
  SelectControl
} from '@wordpress/components';
import {BlockControls,MediaUpload,MediaUploadCheck} from "@wordpress/editor";

import {Preview} from '../../components/Preview';
import {ImageOrButton} from '../../components/ImageOrButton/ImageOrButton';
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';

const TextControl = withCharacterCounter( BaseTextControl );
const TextareaControl = withCharacterCounter( BaseTextareaControl );

export class Splittwocolumns extends Component {
    constructor(props) {
      super(props);
    }

    renderEdit() {
      let tag_list   = this.props.tagsList.map((tag) => ({label: tag.name, value: tag.id}));
      tag_list.unshift({label: '--Select Tag--', value: 0});

      let issuepage_list = this.props.issuepageList.map((issue) => ({label: issue.title.raw, value: issue.id}));
      issuepage_list.unshift({label: '--Select Issue--', value: 0});

      const {__} = wp.i18n;

      const {
        select_issue,
        title,
        issue_description,
        issue_link_text,
        issue_link_path,
        issue_image,
        focus_issue_image,
        select_tag,
        tag_description,
        button_text,
        button_link,
        tag_image,
        focus_tag_image,
        issue_image_url,
        tag_image_url
      } = this.props;

      // Convert focal point values from : 10% 80% => {x:0.1, y:0.8}
      let focus_issue_image_obj = {x: 0.5,y: 0.5};
      if (focus_issue_image) {
        let [x,y] = focus_issue_image.replace(/\%/g, '').split(' ');
        focus_issue_image_obj = {x: (x/100), y: (y/100)}
      }

      // Convert focal point values from : 10% 80% => {x:0.1, y:0.8}
      let focus_tag_image_obj = {x: 0.5,y: 0.5};
      if (focus_tag_image) {
        let [x,y] = focus_tag_image.replace(/\%/g, '').split(' ');
        focus_tag_image_obj = {x: (x/100), y: (y/100)}
      }

      const dimensions = {width: 400, height: 100};

      return (
        <Fragment>
          <h3>{__('Issue fields (Column 1 - Left side)', 'p4ge')}</h3>
          <div>
            {issuepage_list &&
              <SelectControl
                label={__('Select an issue', 'p4ge')}
                value={select_issue}
                options={issuepage_list}
                onChange={this.props.onSelectIssue}
              />
            }
            <TextControl
              label={__('Issue Title', 'p4ge')}
              placeholder={__('Enter Title', 'p4ge')}
              value={title}
              onChange={this.props.onIssueTitleChange}
              help={__('(Optional) Fill this only if you need to override issue title.', 'p4ge')}
              characterLimit={40}
            />
            <TextareaControl
              label={__('Issue Description', 'p4ge')}
              placeholder={__('Enter Description', 'p4ge')}
              help={__('(Optional) Fill this only if you need to override issue description.', 'p4ge')}
              value={issue_description}
              onChange={this.props.onIssueDescriptionChange}
              characterLimit={400}
            />
            <TextControl
              label={__('Issue link text', 'p4ge')}
              placeholder={__('Enter link text', 'p4ge')}
              value={issue_link_text}
              onChange={this.props.onIssueLinkTextChange}
              help={__('(Optional)', 'p4ge')}
            />
            <TextControl
              label={__('Issue link path', 'p4ge')}
              placeholder={__('Enter link path', 'p4ge')}
              value={issue_link_path}
              onChange={this.props.onIssueLinkPathChange}
              help={__('(Optional)', 'p4ge')}
            />
            {__('Issue Image', 'p4ge')}
            <ImageOrButton
              title={__('Select Image for Issue', 'p4ge')}
              onSelectImage={this.props.onSelectIssueImage}
              imageId={issue_image}
              imageUrl={issue_image_url}
              buttonLabel={__('+ Select Image for Issue', 'p4ge')}
              help={__('(Optional)', 'p4ge')}
              imgClass='splittwocolumns-block-issue-imgs'
            />

            {issue_image &&
            <div>
              {__('Select focal point for issue image', 'p4ge')}
              <FocalPointPicker
                url={issue_image_url}
                dimensions={dimensions}
                value={focus_issue_image_obj}
                onChange={this.props.onIssueImageFocalPointChange}
              />
              {__('(Optional)', 'p4ge')}
            </div>
            }
          </div>
          <hr />
          <h3>{__('Campaign fields (Column 2 - Right side)', 'p4ge')}</h3>
          <div>
            {tag_list &&
              <SelectControl
                label={__('Select a tag', 'p4ge')}
                value={select_tag}
                options={tag_list}
                onChange={this.props.onSelectTag}
              />
            }
            <TextareaControl
              label={__('Campaign Description', 'p4ge')}
              placeholder={__('Enter Description', 'p4ge')}
              help={__('(Optional)', 'p4ge')}
              value={tag_description}
              onChange={this.props.onTagDescriptionChange}
              characterLimit={400}
            />
            <TextControl
              label={__('Campaign button text', 'p4ge')}
              placeholder={__('Enter button text', 'p4ge')}
              value={button_text}
              onChange={this.props.onButtonTextChange}
              help={__('(Optional)', 'p4ge')}
            />
            <TextControl
              label={__('Campaign button link', 'p4ge')}
              placeholder={__('Enter button link', 'p4ge')}
              value={button_link}
              onChange={this.props.onButtonLinkChange}
              help={__('(Optional)', 'p4ge')}
            />
            {__('Campaign Image', 'p4ge')}
            <ImageOrButton
              title={__('Select Image for Campaign', 'p4ge')}
              onSelectImage={this.props.onSelectCampaignImage}
              imageId={tag_image}
              imageUrl={tag_image_url}
              buttonLabel={__('+ Select Image for Campaign', 'p4ge')}
              help={__('(Optional)', 'p4ge')}
              imgClass='splittwocolumns-block-tag_imgs'
            />

            {tag_image &&
            <div>
              {__('Select focal point for campaign image', 'p4ge')}
              <FocalPointPicker
                url={tag_image_url}
                dimensions={dimensions}
                value={focus_tag_image_obj}
                onChange={this.props.onCampaignImageFocalPointChange}
              />
              {__('(Optional)', 'p4ge')}
            </div>
            }
          </div>
          <hr />
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
                  block={'planet4-blocks/split-two-columns'}
                  attributes={{
                    select_issue: this.props.select_issue,
                    title: this.props.title,
                    issue_description: this.props.issue_description,
                    issue_link_text: this.props.issue_link_text,
                    issue_link_path: this.props.issue_link_path,
                    issue_image: this.props.issue_image,
                    focus_issue_image: this.props.focus_issue_image,
                    select_tag: this.props.select_tag,
                    tag_description: this.props.tag_description,
                    button_text: this.props.button_text,
                    button_link: this.props.button_link,
                    tag_image: this.props.tag_image,
                    focus_tag_image: this.props.focus_tag_image,
                  }}>
                </ServerSideRender>
              </Preview>
          </div>
      );
    }
}
