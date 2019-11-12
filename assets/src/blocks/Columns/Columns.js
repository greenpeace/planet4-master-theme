import { React } from 'react';
import {Component,Fragment} from "@wordpress/element";
import {
  TextControl,
  TextareaControl,
  ServerSideRender,
  Dashicon,
  Tooltip,
  ToggleControl, Button
} from '@wordpress/components';
import {MediaPlaceholder, MediaUpload, MediaUploadCheck} from "@wordpress/editor";

import {LayoutSelector} from '../../components/LayoutSelector/LayoutSelector';
import {Preview} from '../../components/Preview';

export class Columns extends Component {
    constructor(props) {
      super(props);
    }

    renderEdit() {
      const {__} = wp.i18n;

      const {columns_title,columns_description,columns_block_style,columns,column_img,onDeleteImage} = this.props;

      const getImageOrButton = (openEvent, index) => {
        if ( columns[index] && columns[index]['attachment'] && ( 0 <  columns[index]['attachment'] ) ) {

          return (

            <div align='center'>
              <div>{__('Column %s: Image', 'p4ge').replace('%s', index+1)}</div>
              <div className="img-wrap">
                <Tooltip text={__('Remove Column Image', 'p4ge')}>
                  <span className="close" onClick={ev => {
                    onDeleteImage(index);
                    ev.stopPropagation()
                  }}>&times;</span>
                </Tooltip>
              <img
                src={ column_img[columns[index]['attachment']] }
                onClick={ openEvent }
                className='Columns__imgs'
                width={'400px'}
                style={{padding: '10px 10px'}}
              />
              </div>
            </div>

          );
        }
        else {

          return (
            <div className='column-img-btn-container'>
              <div className='column-img-label'>{__('Column %s: Image', 'p4ge').replace('%s', index+1)}</div>
              <Button
                onClick={ openEvent }
                className='button'>
                + {__('Select Column %s: Image', 'p4ge').replace('%s', index+1)}
              </Button>
            </div>
          );
        }
      };

      return (
        <Fragment>
          <h3>{__('What style of column do you need?', 'p4ge')}</h3>

          <div>
            <LayoutSelector
              selectedOption={columns_block_style}
              onSelectedLayoutChange={this.props.onSelectedLayoutChange}
              options={[
                {
                  label: __('No Image', 'p4ge'),
                  image: window.p4ge_vars.home + 'images/columns-no_images.jpg',
                  value: 'no_image',
                  help: __('Optional headers, description text and buttons in a column display.', 'p4ge')
                },
                {
                  label: __('Tasks', 'p4ge'),
                  image: window.p4ge_vars.home + 'images/columns-tasks.jpg',
                  value: 'tasks',
                  help: __('Used on Take Action pages, this display has ordered tasks, and call to action buttons.', 'p4ge')
                },
                {
                  label: __('Icons', 'p4ge'),
                  image: window.p4ge_vars.home + 'images/columns-icons.jpg',
                  value: 'icons',
                  help: __('For more static content, this display has an icon, header, description and text link.', 'p4ge')
                },
                {
                  label: __('Images', 'p4ge'),
                  image: window.p4ge_vars.home + 'images/columns-images.jpg',
                  value: 'image',
                  help: __('For more static content, this display has an image, header, description and text link.', 'p4ge')
                },
              ]}
            />
          </div>
          <div>
            <TextControl
              label={__('Title', 'p4ge')}
              placeholder={__('Enter block title', 'p4ge')}
              value={columns_title}
              onChange={this.props.onTitleChange}
            />
            <TextareaControl
              label={__('Description', 'p4ge')}
              placeholder={__('Enter block description', 'p4ge')}
              value={columns_description}
              onChange={this.props.onDescriptionChange}
            />
          </div>
          <div>

            {columns.map((item, index) => {
              return (
                <div key={index}>
                  <div><hr /><i>{__('In order for the column to appear at least <strong>Header or Body</strong> has to be filled.', 'p4ge')}</i></div>

                  <TextControl
                    label={__('Column %s: Header', 'p4ge').replace('%s', index+1)}
                    placeholder={__('Enter header of %s column', 'p4ge').replace('%s', index+1)}
                    value={item.title}
                    onChange={this.props.onColumnHeaderChange.bind(this,index)}
                  />
                  <TextareaControl
                    label={__('Column %s: Body', 'p4ge').replace('%s', index+1)}
                    placeholder={__('Enter body of %s column', 'p4ge').replace('%s', index+1)}
                    value={item.description}
                    onChange={this.props.onColumnDescriptionChange.bind(this,index)}
                  />

                  { 'no_image' != columns_block_style &&
                    <div className='components-base-control'>
                      <MediaUploadCheck>
                        <MediaUpload
                          type='image'
                          onSelect={this.props.onSelectImage.bind(this,index)}
                          value={item.attachment}
                          allowedTypes={['image']}
                          render={ ({ open }) => getImageOrButton(open, index) }
                        />
                      </MediaUploadCheck>
                    </div>
                  }

                  <TextControl
                    label={__('Column %s: Button/CtA Link', 'p4ge').replace('%s', index+1)}
                    placeholder={__('Enter link for column %s', 'p4ge').replace('%s', index+1)}
                    value={item.cta_link}
                    onChange={this.props.onCTALinkChange.bind(this,index)}
                  />
                  <ToggleControl
                    label={__('Open link in new tab', 'p4ge')}
                    help={__('Open Column %s: Button/CtA Link in a new tab', 'p4ge').replace('%s', index+1)}
                    value={item.link_new_tab}
                    checked={item.link_new_tab}
                    onChange={this.props.onLinkNewTabChange.bind(this,index)}
                  />
                  <TextControl
                    label={__('Column %s: Button/CtA Text', 'p4ge').replace('%s', index+1)}
                    placeholder={__('Enter text of button/link for column %s', 'p4ge').replace('%s', index+1)}
                    value={item.cta_text}
                    onChange={this.props.onCTAButtonTextChange.bind(this,index)}
                  />
                </div>
              );
            })}

            { columns.length < 4 && (
              <Tooltip text={__('Add Column', 'p4ge')}>
                <button
                  className={
                    "wp-block-p4ge-blocks-columns__addIcon"
                  }
                  onClick={this.props.addColumn}
                >
                  {__('Add Column', 'p4ge')} <Dashicon icon={"plus"} size={12} />
                </button>
              </Tooltip>
            )}

            { columns.length > 0 && (
              <Tooltip text={__('Remove Column', 'p4ge')}>
                <button
                  className={
                    "wp-block-p4ge-blocks-columns__removeIcon"
                  }
                  onClick={this.props.removeColumn}
                >
                  {__('Remove Column', 'p4ge')} <Dashicon icon={"minus"} size={12} />
                </button>
              </Tooltip>
            )}
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
                  block={'planet4-blocks/columns'}
                  attributes={{
                    columns_block_style: this.props.columns_block_style,
                    columns_title: this.props.columns_title,
                    columns_description: this.props.columns_description,
                    columns: this.props.columns,
                  }}>
                </ServerSideRender>
              </Preview>
          </div>
      );
    }
}
