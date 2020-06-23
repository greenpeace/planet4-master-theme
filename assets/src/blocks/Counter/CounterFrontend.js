import { Component, Fragment } from '@wordpress/element';

export class CounterFrontend extends Component {
  render() {
    const {
      className,
      title,
      description,
      text
    } = this.props;

    // Calculate percent and remaining values depending on completed and target props
    let { target, completed } = this.props;
    let remaining = 0;
    let percent = 0;
    if (target === undefined) target = 0;
    if (completed === undefined) completed = 0;
    if (target > 0 || completed > 0) {
      remaining = target - completed;
      percent = (completed / target) * 100;
    }

    let style = 'plain';
    if (className) style = className.split('is-style-')[1];
    let arcLength = 31.5;

    const COUNTER_TEXT = {
      '%completed%': `<span className="counter-target">${completed}</span>`,
      '%target%': `<span className="counter-target">${target}</span>`,
      '%remaining%': `<span className="counter-target">${remaining}</span>`
    };

    return (
      <Fragment>
        <section className={`block container counter-block counter-style-${style}`}>
          <div className="container">
            {title &&
              <header>
                <h2 className="page-section-header">{title}</h2>
              </header>
            }
            {description &&
              <p className="page-section-description" dangerouslySetInnerHTML={{ __html: description }} />
            }
          </div>
          <div className="content-counter">
            {(style === 'bar' || style === 'en-forms-bar') &&
              <div className="progress-container">
                <div className={`progress-bar ${style === 'en-forms-bar' ? 'enform-progress-bar' : ''}`} style={{ width: `calc(${percent}% + 20px)` }} />
              </div>
            }
            {style === 'arc' &&
              <svg className="progress-arc" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 24 14">
                <path className="background" d="M 2 12 A 1 1 0 1 1 22 12" />
                <path className="foreground" d="M 2 12 A 1 1 0 1 1 22 12"
                  strokeDasharray={arcLength}
                  strokeDashoffset={`${(1 - percent / 100) * arcLength}`} />
              </svg>
            }
            {text &&
              <div
                className={`counter-text ${100 <= percent ? 'counter-text-goal_reached' : ''}`}
                dangerouslySetInnerHTML={{ __html: text.replace(/%completed%|%target%|%remaining%/gi, match => COUNTER_TEXT[match]) }}
              />
            }
          </div>
        </section>
      </Fragment>
    )
  }
}
