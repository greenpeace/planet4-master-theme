import { Component, Fragment } from '@wordpress/element';

export class CounterFrontend extends Component {
  render() {
    let style = 'plain';
    if (this.props.className) style = this.props.className.split('is-style-')[1];
    let arcLength = '31.5%';

    const COUNTER_TEXT = {
      '%completed%': '<span className="counter-target">' + this.props.completed + '</span>',
      '%target%': '<span className="counter-target">' + this.props.target + '</span>',
      '%remaining%': '<span className="counter-target">' + this.props.remaining + '</span>'
    };

    return (
      <Fragment>
        <section className={`block container counter-block counter-style-${style}`}>
          <div className="container">
            {this.props.title &&
              <header>
                <h2 className="page-section-header">{this.props.title}</h2>
              </header>
            }
            {this.props.description &&
              <p className="page-section-description" dangerouslySetInnerHTML={{ __html: this.props.description }} />
            }
          </div>
          <div className="content-counter">
            {style === 'bar' || style === 'en-forms-bar' &&
              <div className="progress-container">
                <div className={`progress-bar ${style === 'en-forms-bar' ? 'enform-progress-bar' : ''}`} style={`width: calc(${this.props.percent}% + 20px);`}></div>
              </div>
            }
            {style === 'arc' &&
              <svg className="progress-arc" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 24 14">
                <path className="background" d="M 2 12 A 1 1 0 1 1 22 12" />
                <path className="foreground" d="M 2 12 A 1 1 0 1 1 22 12"
                  strokeDasharray={arcLength}
                  strokeDashoffset={`${(1 - this.props.percent / 100) * arcLength}`} />
              </svg>
            }
            {this.props.text &&
              <div
                className={`counter-text ${100 <= this.props.percent ? 'counter-text-goal_reached' : ''}`}
                dangerouslySetInnerHTML={{ __html: this.props.text.replace(/%completed%|%target%|%remaining%/gi, match => COUNTER_TEXT[match]) }}
              />
            }
          </div>
        </section>
      </Fragment>
    )
  }
}
