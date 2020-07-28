import { FocalPointPicker, PanelBody, SelectControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { ImageOrButton } from '../../components/ImageOrButton/ImageOrButton';
import { URLInput } from "../../components/URLInput/URLInput";

const { useSelect } = wp.data;
const { __ } = wp.i18n;

/**
 * Sidebar settings 
 */
export const SplittwocolumnsSettings = ({attributes, charLimit, setAttributes}) => {
  const issuesList = useSelect((select) => {
    const parent_page = window.p4ge_vars.planet4_options.explore_page;
    const issue_page_args = {
      per_page: -1,
      sort_order: 'asc',
      sort_column: 'post_title',
      parent: parent_page,
      post_status: 'publish',
    };
    return select('core').getEntityRecords('postType', 'page', issue_page_args) || [];
  }, []);

  const tagsList = useSelect((select) => {
    const taxonomy_args = {hide_empty: false, per_page: 50};
    return select('core').getEntityRecords('taxonomy', 'post_tag', taxonomy_args) || [];
  }, []);

  const onIssueChange = (issue_id) => {
    issue_id = parseInt(issue_id);
    const issue = issues.find(issue => issue.id === issue_id) || null;
    setAttributes({
      'select_issue': issue_id,
      'title': cleanString(
        issue?.cmb2?.p4_metabox.p4_title || issue?.title.raw || attributes.title,
        charLimit.title
      ),
      'issue_description': cleanString(
        issue?.cmb2?.p4_metabox.p4_description || attributes.issue_description,
        charLimit.description
      ),
      'issue_link_text': __('Learn more about this issue', 'planet4-blocks'),
      'issue_link_path': issue?.link || ''
    });
  }

  const onTagChange = (tag_id) => {
    tag_id = parseInt(tag_id);
    const tag = tags.find(tag => tag.id === tag_id) || null;
    setAttributes({
      'select_tag': tag_id,
      'tag_name': cleanString(tag?.name || '', charLimit.title),
      'tag_description': cleanString(
        tag?.description || attributes.tag_description,
        charLimit.description
      ),
      'tag_link': tag?.link || '',
      'button_text': attributes.button_text || __( 'Get Involved', 'planet4-blocks' ),
      'button_link': attributes.button_link || tag?.link || ''
    });
  }

  const onImageChange = (image_type, image) => {
    setAttributes({
      [image_type + '_id']: parseInt(image.id),
      [image_type + '_src']: image.url,
      [image_type + '_srcset']: '',
      [image_type + '_title']: image.title
    });
  }

  const onFocalChange = (focal_name, {x,y}) => {
    setAttributes({[focal_name]: parseInt(x*100) + '% ' + parseInt(y*100) + '%' });
  }

  let issue_page_list = issuesList.map((issue) => ({label: issue.title.raw, value: issue.id}));
  issue_page_list.unshift({label: '--Select Issue--', value: 0});
  let tag_list = tagsList.map((tag) => ({label: tag.name, value: tag.id}));
  tag_list.unshift({label: '--Select Tag--', value: 0});

  const focus_issue_image_obj = convertFocalStringToObj(attributes.focus_issue_image || null);
  const focus_tag_image_obj = convertFocalStringToObj(attributes.focus_tag_image || null);
  const focal_picker_dimensions = {width: 400, height: 100};

  return (
    <InspectorControls>
      <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
        <div>
          {issue_page_list &&
            <SelectControl
              label={__('Select an issue', 'planet4-blocks-backend')}
              value={attributes.select_issue}
              options={issue_page_list}
              onChange={(issue_id) => {onIssueChange(issue_id)}}
            />
            }
        </div>
        <div>
          <URLInput
            label={__('Issue link path', 'planet4-blocks-backend')}
            placeholder={__('Enter link path', 'planet4-blocks-backend')}
            value={attributes.issue_link_path}
            onChange={value => setAttributes({issue_link_path: value})}
            help={__('(Optional)', 'planet4-blocks-backend')}
          />
        </div>
        <div>
          {__('Issue Image', 'planet4-blocks-backend')}
          <ImageOrButton
            title={__('Select Image for Issue', 'planet4-blocks-backend')}
            onSelectImage={(image) => {onImageChange('issue_image', image)}}
            imageId={attributes.issue_image_id}
            imageUrl={attributes.issue_image_src}
            buttonLabel={__('+ Select Image for Issue', 'planet4-blocks-backend')}
            help={__('(Optional)', 'planet4-blocks-backend')}
            imgClass='splittwocolumns-block-issue-imgs'
          />
        </div>
        {attributes.issue_image_src &&
        <div>
          {__('Select focal point for issue image', 'planet4-blocks-backend')}
          <FocalPointPicker
            url={attributes.issue_image_src}
            dimensions={focal_picker_dimensions}
            value={focus_issue_image_obj}
            onChange={(focus) => {onFocalChange('focus_issue_image', focus)}}
          />
          {__('(Optional)', 'planet4-blocks-backend')}
        </div>
        }
        <hr/>
        <div>
          {tag_list &&
            <SelectControl
              label={__('Select a tag', 'planet4-blocks-backend')}
              value={attributes.select_tag}
              options={tag_list}
              onChange={(tag_id) => {onTagChange(tag_id)}}
            />
          }
        </div>
        <div>
          <URLInput
            label={__('Campaign button link', 'planet4-blocks-backend')}
            placeholder={__('Enter button link', 'planet4-blocks-backend')}
            value={attributes.button_link}
            onChange={(value) => setAttributes({'button_link': value})}
            help={__('(Optional)', 'planet4-blocks-backend')}
          />
        </div>
        <div>
          {__('Campaign Image', 'planet4-blocks-backend')}
          <ImageOrButton
            title={__('Select Image for Campaign', 'planet4-blocks-backend')}
            onSelectImage={(image) => {onImageChange('tag_image', image)}}
            imageId={attributes.tag_image_id}
            imageUrl={attributes.tag_image_src}
            buttonLabel={__('+ Select Image for Campaign', 'planet4-blocks-backend')}
            help={__('(Optional)', 'planet4-blocks-backend')}
            imgClass='splittwocolumns-block-tag_imgs'
          />
          {attributes.tag_image_src &&
          <div>
            {__('Select focal point for campaign image', 'planet4-blocks-backend')}
            <FocalPointPicker
              url={attributes.tag_image_src}
              dimensions={focal_picker_dimensions}
              value={focus_tag_image_obj}
              onChange={(focus) => {onFocalChange('focus_tag_image', focus)}}
            />
            {__('(Optional)', 'planet4-blocks-backend')}
          </div>
          }
        </div>
      </PanelBody>
    </InspectorControls>
  );
}

/**
 * Convert focal point values from : 10% 80% => {x:0.1, y:0.8}
 * @param {string} focal_str 
 */
const convertFocalStringToObj = (focal_str) => {
  if (!focal_str || focal_str.length <= 0) {
    return {x: 0.5,y: 0.5};
  }
  let [x, y] = focal_str.replace(/\%/g, '').split(' ');
  return {x: (parseInt(x)/100), y: (parseInt(y)/100)};
}

/**
 * Remove tags, line breaks, and cut to limit
 */
const cleanString = (str, limit) => {
  return str.replace(/<[^>]+>/g, '')
    .replace(/(\r\n\t|\n|\r\t)/gm, '')
    .substr(0, limit);
}
