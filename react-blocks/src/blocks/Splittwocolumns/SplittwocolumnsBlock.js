import {Splittwocolumns} from './Splittwocolumns';

const {__} = wp.i18n;

export class SplittwocolumnsBlock {
    constructor() {
      const {registerBlockType} = wp.blocks;
      const {__} = wp.i18n;
      const { withSelect } = wp.data;

      registerBlockType( 'planet4-blocks/split-two-columns', {
        title: __('Split Two Columns', 'p4ge'),
        icon: 'format-gallery',
        category: 'planet4-blocks',
        /**
         * Transforms old 'shortcake' shortcode to new gutenberg block.
         *
         * old block-shortcode:
         * [shortcake_split_two_columns select_issue="13813" title="lorem ipsum" issue_description="lorem ipsum" issue_link_text="test link"
         *        issue_link_path="http://www.googlw.com" issue_image="23634" focus_issue_image="right top" select_tag="19"
         *        tag_description="lorem ipsum" button_text="btn text" button_link="http://www.google.com" tag_image="23634" focus_tag_image="right top"
         * /]
         *
         * new block-gutenberg:
         * <!-- wp:planet4-blocks/split-two-columns {"select_issue":2079,"title":"Lorem Ipsum","issue_description":"Lorem Ipsum","issue_link_text":"tets link","issue_link_path":"http://www.google.com","issue_image":23634,"focus_issue_image":"33% 60%","select_tag":65,"tag_description":"Lorem Ipsum","button_text":"btn text","button_link":"http://www.google.com","tag_image":23634,"focus_tag_image":"30% 79%"} /-->
         *
         */
        transforms: {
          from: [
            {
              type: 'shortcode',
              // Shortcode tag can also be an array of shortcode aliases
              tag: 'shortcake_split_two_columns',
              attributes: {
                select_issue: {
                  type: 'integer',
                  shortcode: ({named: {select_issue = ''}}) => select_issue,
                },
                title: {
                  type: 'string',
                  shortcode: ({named: {title = ''}}) => title,
                },
                issue_description: {
                  type: 'string',
                  shortcode: ({named: {issue_description = ''}}) => issue_description,
                },
                issue_link_text: {
                  type: 'string',
                  shortcode: ({named: {issue_link_text = ''}}) => issue_link_text,
                },
                issue_link_path: {
                  type: 'string',
                  shortcode: ({named: {issue_link_path = ''}}) => issue_link_path,
                },
                issue_image: {
                  type: 'integer',
                  shortcode: ({named: {issue_image = ''}}) => issue_image,
                },
                focus_issue_image: {
                  type: 'string',
                  shortcode: ({named: {focus_issue_image = ''}}) => focus_issue_image,
                },
                select_tag: {
                  type: 'integer',
                  shortcode: ({named: {select_tag = ''}}) => select_tag,
                },
                tag_description: {
                  type: 'string',
                  shortcode: ({named: {tag_description = ''}}) => tag_description,
                },
                button_text: {
                  type: 'string',
                  shortcode: ({named: {button_text = ''}}) => button_text,
                },
                button_link: {
                  type: 'string',
                  shortcode: ({named: {button_link = ''}}) => button_link,
                },
                tag_image: {
                  type: 'integer',
                  shortcode: ({named: {tag_image = ''}}) => tag_image,
                },
                focus_tag_image: {
                  type: 'string',
                  shortcode: ({named: {focus_tag_image = ''}}) => focus_tag_image,
                },
              },
            },
          ]
        },
        attributes: {
          select_issue: {
            type: 'number',
            default: 0,
          },
          title: {
            type: 'string',
          },
          issue_description: {
            type: 'string',
          },
          issue_link_text: {
            type: 'string',
          },
          issue_link_path: {
            type: 'string',
          },
          issue_image: {
            type: 'number',
          },
          focus_issue_image: {
            type: 'string',
          },
          select_tag: {
            type: 'number',
            default: 0,
          },
          tag_description: {
            type: 'string',
          },
          button_text: {
            type: 'string',
          },
          button_link: {
            type: 'string',
          },
          tag_image: {
            type: 'number',
          },
          focus_tag_image: {
            type: 'string',
          },
        },
        edit: withSelect((select,props) => {
          const tagsTaxonomy = 'post_tag';
          const issuePage    = 'page';

          const taxonomy_args = {
            hide_empty: false,
            per_page: 50,
          };
          const {getEntityRecords} = select('core');

          // We should probably wrap all these in a single call,
          // or maybe use our own way of retrieving data from the
          // API, I don't know how this scales.
          const tagsList = getEntityRecords('taxonomy', tagsTaxonomy, taxonomy_args);

          const issue_page_args = {
            per_page: -1,
            sort_order: 'asc',
            sort_column: 'post_title',
            parent: window.p4ge_vars.planet4_options.explore_page,
            post_status: 'publish',
          };
          const issuepageList = getEntityRecords('postType', issuePage, issue_page_args);

          const { attributes } = props;
          const { issue_image,tag_image } = attributes;

          let issue_image_url = '';
          if (issue_image) {
            issue_image_url = select('core').getMedia(issue_image);
            if ( issue_image_url ) {
              issue_image_url = issue_image_url.media_details.sizes.medium.source_url;
            }
          }

          let tag_image_url = '';
          if (tag_image) {
            tag_image_url = select('core').getMedia(tag_image);
            if ( tag_image_url ) {
              tag_image_url = tag_image_url.media_details.sizes.medium.source_url;
            }
          }

          return {
            tagsList,
            issuepageList,
            issue_image_url,
            tag_image_url,
          };

        } )( ( {
          tagsList,
          issuepageList,
          issue_image_url,
          tag_image_url,
          isSelected,
          attributes,
          setAttributes
        } ) => {

          if (!tagsList || !issuepageList) {
            return "Populating block's fields...";
          }

          if ((tagsList && tagsList.length === 0) || (issuepageList && issuepageList.length === 0)) {
            return "Populating block's fields...";
          }

          function onSelectIssue( value ) {
            setAttributes({select_issue: parseInt(value)});
          }

          function onIssueTitleChange( value ) {
            setAttributes({title: value });
          }

          function onIssueDescriptionChange( value ) {
            setAttributes({issue_description: value});
          }

          function onIssueLinkTextChange( value ) {
            setAttributes({issue_link_text: value });
          }

          function onIssueLinkPathChange( value ) {
            setAttributes({issue_link_path: value});
          }

          function onSelectIssueImage( {id} ) {
            setAttributes({issue_image: id});
          }

          function onIssueImageFocalPointChange( {x,y} ) {
            setAttributes({focus_issue_image: parseInt(x*100)+'% '+parseInt(y*100)+'%' });
          }

          function onSelectTag( value ) {
            setAttributes({select_tag: parseInt(value)});
          }

          function onTagDescriptionChange( value ) {
            setAttributes({tag_description: value});
          }

          function onButtonTextChange( value ) {
            setAttributes({button_text: value });
          }

          function onButtonLinkChange( value ) {
            setAttributes({button_link: value});
          }

          function onSelectCampaignImage( {id} ) {
            setAttributes({tag_image: id});
          }

          function onCampaignImageFocalPointChange( {x,y} ) {
            setAttributes({focus_tag_image: parseInt(x*100)+'% '+parseInt(y*100)+'%' });
          }

          return <Splittwocolumns
            {...attributes}
            isSelected={isSelected}
            tagsList={tagsList}
            issuepageList={issuepageList}
            issue_image_url={issue_image_url}
            tag_image_url={tag_image_url}
            onSelectIssue={onSelectIssue}
            onIssueTitleChange={onIssueTitleChange}
            onIssueDescriptionChange={onIssueDescriptionChange}
            onIssueLinkTextChange={onIssueLinkTextChange}
            onIssueLinkPathChange={onIssueLinkPathChange}
            onSelectIssueImage={onSelectIssueImage}
            onIssueImageFocalPointChange={onIssueImageFocalPointChange}
            onSelectTag={onSelectTag}
            onTagDescriptionChange={onTagDescriptionChange}
            onButtonTextChange={onButtonTextChange}
            onButtonLinkChange={onButtonLinkChange}
            onSelectCampaignImage={onSelectCampaignImage}
            onCampaignImageFocalPointChange={onCampaignImageFocalPointChange}
          />
        }),
        save() {
          return null;
        }
      });
    };
}
