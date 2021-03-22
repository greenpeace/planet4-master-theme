import { Columns } from './Columns';

export const ColumnsFrontend = ({ columns_block_style, columns_title, columns_description }) => {
  const postType = document.body.getAttribute('data-post-type');

  const columns = [];

  return (
    <section className={`block columns-block block-style-${columns_block_style}`}>
      <div className='container'>
        {columns_title &&
          <header>
            <h2 className='page-section-header' dangerouslySetInnerHTML={{ __html: columns_title }} />
          </header>
        }
        {columns_description &&
          <div className='page-section-description' dangerouslySetInnerHTML={{ __html: columns_description }} />
        }
      </div>
      <Columns
        columns_block_style={columns_block_style}
        columns={columns}
        isCampaign={postType === 'campaign'}
      />
    </section>
  );
}
