import { Covers } from './Covers';
import { useCovers } from './useCovers';
import { getCoversClassName } from './getCoversClassName';

export const CoversFrontend = (attributes) => {
  const { covers_view, cover_type, title, description } = attributes;
  const blockClassName = getCoversClassName(cover_type, covers_view);

  const { covers, loading, loadMoreCovers, row } = useCovers(attributes, document.body.dataset.nro);

  const coversProps = {
    covers,
    covers_view,
    row,
    loadMoreCovers,
    cover_type,
  };

  if (loading || !covers.length) {
    return null;
  }

  return (
    <section className={blockClassName}>
      {title &&
        <h2 class="page-section-header" dangerouslySetInnerHTML={{ __html: title }} />
      }
      {description &&
        <div class="page-section-description" dangerouslySetInnerHTML={{ __html: description }} />
      }
      <Covers {...coversProps} />
    </section>
  );
}

