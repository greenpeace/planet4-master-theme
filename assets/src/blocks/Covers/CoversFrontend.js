import { Covers } from './Covers';
import { getCoversClassName } from './getCoversClassName';

export const CoversFrontend = (attributes) => {
  const { covers_view, cover_type, title, description } = attributes;
  const blockClassName = getCoversClassName(cover_type, covers_view);

  return (
    <section className={blockClassName}>
      {title &&
        <h2 class="page-section-header" dangerouslySetInnerHTML={{ __html: title }} />
      }
      {description &&
        <div class="page-section-description" dangerouslySetInnerHTML={{ __html: description }} />
      }
      <Covers {...attributes} />
    </section>
  );
}

