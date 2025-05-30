import {getStyleFromClassName} from '../../functions/getStyleFromClassName';

const {Component} = wp.element;

export class CounterFrontend extends Component {
  constructor(props) {
    super(props);
    this.state = {
      remaining: 0,
      completed: 0,
    };

    this.calculateRemaining = this.calculateRemaining.bind(this);
    this.getCounterText = this.getCounterText.bind(this);
  }

  componentDidMount() {
    const {completed_api} = this.props;
    // Calculate completed and remaining values depending on props
    const counter = this;
    counter.calculateRemaining();
    // Add an eventListener to the window to enable instantly updating counters with supported APIs
    if (completed_api && completed_api.startsWith('https://')) {
      window.addEventListener('updateCounter', counter.calculateRemaining, false);
    }
  }

  componentWillUnmount() {
    const {completed_api} = this.props;
    const counter = this;
    if (completed_api && completed_api.startsWith('https://')) {
      window.removeEventListener('updateCounter', counter.calculateRemaining, false);
    }
  }

  componentDidUpdate({target: prevTarget, completed: prevCompleted, completed_api: prevCompletedApi}) {
    // Update completed and remaining values depending on props
    const {target, completed, completed_api} = this.props;
    if (target !== prevTarget || completed !== prevCompleted || completed_api !== prevCompletedApi) {
      this.calculateRemaining();
    }
  }

  calculateRemaining() {
    const {completed_api} = this.props;
    const target = Math.max(this.props.target, 0);
    let completed = Math.max(this.props.completed, 0);
    let remaining = 0;
    if (completed_api && completed_api.startsWith('https://')) {
      fetch(completed_api)
        .then(response => response.json())
        .then(({unique_count}) => {
          if (unique_count) {
            completed = Math.max(unique_count, 0);
            this.setState({
              completed,
              remaining: Math.max(target - completed, 0),
            });
          }
        }).catch(() => {
          // eslint-disable-next-line no-console
          console.log('Error: Fetching api response...');
        });
    } else {
      remaining = Math.max(target - completed, 0);
      this.setState({remaining, completed});
    }
  }

  getCounterText() {
    const {text, target} = this.props;
    const {remaining, completed} = this.state;
    const COUNTER_TEXT = {
      '%completed%': `<span class="counter-target">${completed}</span>`,
      '%target%': `<span class="counter-target">${target || 0}</span>`,
      '%remaining%': `<span class="counter-target">${remaining}</span>`,
    };

    return text.replace(/%completed%|%target%|%remaining%/gi, match => COUNTER_TEXT[match]);
  }

  render() {
    const {
      className,
      title,
      description,
      text,
      target,
      isEditing,
    } = this.props;

    const {completed} = this.state;

    let style = this.props.style || 'plain'; // Needed to convert existing blocks
    const styleClass = getStyleFromClassName(className);
    if (styleClass) {
      style = styleClass;
    }

    const percent = Math.min(target > 0 ? Math.round(completed / target * 100) : 0, 100);

    let counterClassName = `block counter-block counter-style-${style} ${className ?? ''}`;
    if (isEditing) {
      counterClassName += ' editing';
    }

    return (
      <section className={counterClassName}>
        {title && !isEditing &&
          <header>
            <h2 className="page-section-header">{title}</h2>
          </header>
        }
        {description && !isEditing &&
          <p className="page-section-description" dangerouslySetInnerHTML={{__html: description}} />
        }
        <div className="content-counter">
          {style === 'bar' &&
            <div className="progress-container">
              <div className="progress-bar" style={{width: `calc(${percent}% + 20px)`}} />
            </div>
          }
          {text &&
            <div
              className={`counter-text ${100 <= percent ? 'counter-text-goal_reached' : ''}`}
              role="presentation"
              dangerouslySetInnerHTML={{__html: this.getCounterText()}}
            />
          }
        </div>
      </section>
    );
  }
}
