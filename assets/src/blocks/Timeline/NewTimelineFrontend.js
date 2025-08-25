export const NewTimelineFrontend = ({attributes}) => {
  const {
    timeline_title,
    description,
    className,
    google_sheets_url,
  } = attributes;

  return (
    <section className={`block timeline-block ${className ?? ''}`}>
      {!!timeline_title &&
        <header>
          <h2 className="page-section-header">{ timeline_title }</h2>
        </header>
      }
      {!!description &&
        <p className="page-section-description" dangerouslySetInnerHTML={{__html: description}} />
      }
      <p>This is the new Timeline block! {`${google_sheets_url}`}</p>
    </section>
  );
};
