import {FocalPointPicker, PanelBody, SelectControl} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import {ImageOrButton} from '../../components/ImageOrButton/ImageOrButton';
import {URLInput} from '../../components/URLInput/URLInput';

const {useSelect} = wp.data;
const {__} = wp.i18n;

// Sidebar settings
export const SplitTwoColumnsSettings = ({attributes, charLimit, setAttributes}) => {
  const {
    title,
    select_issue,
    issue_description,
    issue_link_path,
    issue_link_text,
    issue_image_id,
    issue_image_src,
    focus_issue_image,
    select_tag,
    tag_description,
    button_text,
    button_link,
    tag_image_id,
    tag_image_src,
    focus_tag_image,
    edited,
  } = attributes;

  const issuesList = useSelect(select => {
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

  const tagsList = useSelect(select => {
    const taxonomy_args = {hide_empty: false, per_page: 50};
    return select('core').getEntityRecords('taxonomy', 'post_tag', taxonomy_args) || [];
  }, []);

  const onIssueChange = issue_id => {
    const issue = issuesList.find(issueFound => issueFound.id === parseInt(issue_id)) || null;

    setAttributes({
      select_issue: parseInt(issue_id),
      title: edited.title ? title : cleanString(
        issue?.cmb2?.p4_metabox.p4_title || issue?.title.raw || title,
        charLimit.title
      ),
      issue_description: edited.issue_description ? issue_description : cleanString(
        issue?.cmb2?.p4_metabox.p4_description ?? issue_description,
        charLimit.description
      ),
      issue_link_text: edited.issue_link_text ? issue_link_text :
        issue_link_text || __('Learn more about this issue', 'planet4-blocks'),
      issue_link_path: issue?.link || '',
      issue_image_id: edited.issue_image_id ? issue_image_id : (issue?.featured_media ?? 0),
    });
  };

  const onTagChange = tag_id => {
    const tag = tagsList.find(tagFound => tagFound.id === parseInt(tag_id));

    setAttributes({
      select_tag: parseInt(tag_id),
      tag_name: cleanString(tag?.name || '', charLimit.title),
      tag_description: edited.tag_description ? tag_description : cleanString(
        tag?.description || tag_description,
        charLimit.description
      ),
      button_text: edited.button_text ? button_text :
        button_text || __('Get involved', 'planet4-blocks'),
      tag_link: tag?.link || '',
      button_link: button_link || tag?.link || '',
      tag_image_id: edited.tag_image_id ? tag_image_id : (tag?.meta?.tag_attachment_id ?? 0),
    });
  };

  const onImageChange = (image_type, image) => {
    setAttributes({
      [`${image_type}_id`]: parseInt(image?.id) ?? 0,
      [`${image_type}_src`]: image?.url ?? image?.source_url ?? '',
      [`${image_type}_title`]: image?.title?.raw ?? image?.title ?? '',
      edited: {
        ...edited,
        [`${image_type}_id`]: true,
      },
    });
  };

  const onFocalChange = (focal_name, {x, y}) => {
    setAttributes({[focal_name]: `${x * 100}% ${y * 100}%`});
  };

  const issueOptions = [
    {label: '--Select Issue--', value: 0},
    ...issuesList.map(issue => ({label: issue.title.raw, value: issue.id})),
  ];
  const tagOptions = [
    {label: '--Select Tag--', value: 0},
    ...tagsList.map(tag => ({label: tag.name, value: tag.id})),
  ];

  const focus_issue_image_obj = convertFocalStringToObj(focus_issue_image);
  const focus_tag_image_obj = convertFocalStringToObj(focus_tag_image);
  const focal_picker_dimensions = {width: 400, height: 100};

  return (
    <InspectorControls>
      <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
        <div>
          {issueOptions &&
            <SelectControl
              label={__('Select an issue', 'planet4-blocks-backend')}
              value={select_issue}
              options={issueOptions}
              onChange={onIssueChange}
            />
          }
        </div>
        <div>
          <URLInput
            label={__('Issue link path', 'planet4-blocks-backend')}
            placeholder={__('Enter link path', 'planet4-blocks-backend')}
            value={issue_link_path}
            onChange={value => setAttributes({issue_link_path: value})}
            help={__('(Optional)', 'planet4-blocks-backend')}
          />
        </div>
        <div>
          {__('Issue Image', 'planet4-blocks-backend')}
          <ImageOrButton
            title={__('Select Image for Issue', 'planet4-blocks-backend')}
            onSelectImage={image => {
              onImageChange('issue_image', image);
            }}
            imageId={issue_image_id}
            imageUrl={issue_image_src}
            buttonLabel={__('+ Select Image for Issue', 'planet4-blocks-backend')}
            help={__('(Optional)', 'planet4-blocks-backend')}
            imgClass="splittwocolumns-block-issue-imgs"
            disabled={false}
          />
        </div>
        {issue_image_src &&
        <div>
          {__('Select focal point for issue image', 'planet4-blocks-backend')}
          <FocalPointPicker
            url={issue_image_src}
            dimensions={focal_picker_dimensions}
            value={focus_issue_image_obj}
            onChange={focus => {
              onFocalChange('focus_issue_image', focus);
            }}
          />
          {__('(Optional)', 'planet4-blocks-backend')}
        </div>
        }
        <hr />
        <div>
          {tagOptions &&
            <SelectControl
              label={__('Select a tag', 'planet4-blocks-backend')}
              value={select_tag}
              options={tagOptions}
              onChange={onTagChange}
            />
          }
        </div>
        <div>
          <URLInput
            label={__('Campaign button link', 'planet4-blocks-backend')}
            placeholder={__('Enter button link', 'planet4-blocks-backend')}
            value={button_link}
            onChange={value => setAttributes({button_link: value})}
            help={__('(Optional)', 'planet4-blocks-backend')}
          />
        </div>
        <div>
          {__('Campaign Image', 'planet4-blocks-backend')}
          <ImageOrButton
            title={__('Select Image for Campaign', 'planet4-blocks-backend')}
            onSelectImage={image => {
              onImageChange('tag_image', image);
            }}
            imageId={tag_image_id}
            imageUrl={tag_image_src}
            buttonLabel={__('+ Select Image for Campaign', 'planet4-blocks-backend')}
            help={__('(Optional)', 'planet4-blocks-backend')}
            imgClass="splittwocolumns-block-tag_imgs"
            disabled={false}
          />
          {tag_image_src &&
          <div>
            {__('Select focal point for campaign image', 'planet4-blocks-backend')}
            <FocalPointPicker
              url={tag_image_src}
              dimensions={focal_picker_dimensions}
              value={focus_tag_image_obj}
              onChange={focus => {
                onFocalChange('focus_tag_image', focus);
              }}
            />
            {__('(Optional)', 'planet4-blocks-backend')}
          </div>
          }
        </div>
      </PanelBody>
    </InspectorControls>
  );
};

/**
 * Convert focal point values from : 10% 80% => {x:0.1, y:0.8}
 *
 * @param {string} focal_str
 * @return {Object} vector points
 */
const convertFocalStringToObj = focal_str => {
  if (!focal_str) {
    return {x: 0.5, y: 0.5};
  }
  const [x, y] = focal_str.replace(/%/g, '').split(' ');

  return {x: ((parseInt(x) || 0) / 100), y: ((parseInt(y) || 0) / 100)};
};

/**
 * Remove tags, line breaks, and cut to limit
 *
 * @param {string} str
 * @param {number} limit
 * @return {string} cleanString
 */
const cleanString = (str, limit) => {
  return str.replace(/<[^>]+>/g, '')
    .replace(/(\r\n\t|\n|\r\t)/gm, '')
    .substr(0, limit);
};
