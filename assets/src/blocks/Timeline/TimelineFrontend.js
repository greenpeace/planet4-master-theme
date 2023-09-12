import {Timeline} from './Timeline';

export const TimelineFrontend = ({attributes}) => {
  const {
    timeline_title,
    description,
    className,
    ...nodeProps
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
      <Timeline {...nodeProps} />
    </section>
  );
};
