import {useEffect} from '@wordpress/element';
import {LAYOUT_TASKS} from './ColumnConstants';
import {Columns} from './Columns';

export const ColumnsFrontend = ({columns_block_style, columns_title, columns_description, columns, className}) => {
  const postType = document.body.getAttribute('data-post-type');

  // This function updates the headings' height so that they align
  const alignColumnHeaders = () => {
    // On mobile, columns are not side by side so no need to adjust the headers' height
    // For Tasks style, we have an "accordion" for screens up to 992px
    if (window.innerWidth < 768 || (columns_block_style === LAYOUT_TASKS && window.innerWidth < 992)) {
      const headings = [...document.querySelectorAll('.columns-block .column-wrap h3, .columns-block .column-wrap h5')];
      headings.forEach(heading => heading.style.minHeight = 'auto');
      return;
    }

    const columnBlocks = [...document.querySelectorAll('.columns-block')];
    columnBlocks.forEach(columnBlock => {
      // The Tasks style uses h5 tags for its headers, other styles use h3
      const headings = [...columnBlock.querySelectorAll('.column-wrap h3, .column-wrap h5')];
      const highestHeadingHeight = Math.max(...headings.map(heading => heading.offsetHeight));

      headings.forEach(heading => heading.style.minHeight = `${highestHeadingHeight}px`);
    });
  };

  // Align column headers,
  useEffect(() => {
    alignColumnHeaders();
    window.addEventListener('resize', alignColumnHeaders);
    window.addEventListener('load', alignColumnHeaders);
    return () => {
      window.removeEventListener('resize', alignColumnHeaders);
      window.removeEventListener('load', alignColumnHeaders);
    };
  }, []);

  return (
    <section className={`block columns-block block-style-${columns_block_style} ${className ?? ''}`}>
      {columns_title &&
        <header>
          <h2 className="page-section-header" dangerouslySetInnerHTML={{__html: columns_title}} />
        </header>
      }
      {columns_description &&
        <p className="page-section-description" dangerouslySetInnerHTML={{__html: columns_description}} />
      }
      <Columns
        columns_block_style={columns_block_style}
        columns={columns}
        isCampaign={postType === 'campaign'}
      />
    </section>
  );
};
