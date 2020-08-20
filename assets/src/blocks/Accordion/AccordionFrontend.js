/* eslint-disable camelcase */
import { Component, Fragment } from '@wordpress/element';

window.$ = $ || jQuery;
window.dataLayer = window.dataLayer || [];

export class AccordionFrontend extends Component {
  constructor (props) {
    super(props);

    this.state = {
      // accordions: [],
      // select: '',
      isToggleOn: false
    };
    this.handleCollapseClick = this.handleCollapseClick.bind(this);
    this.handleReadMoreClick = this.handleReadMoreClick.bind(this);
    this.onChangeContent = this.onChangeContent.bind(this);
  }

  // {/* Toggle panels accordion - bug event opens all on click */}
  handleCollapseClick () {
  //handleCollapseClick(index) {
    this.setState(state => ({
      isToggleOn: !state.isToggleOn
      // select:this.state.accordions[index]
    }));

    window.onclick = e => {
      const txt = $(e.target).text().substring(0, 50) + '...';
      if (this.state.isToggleOn === false) {
        $('.accordion').next($('.panel')).addClass('visibility');

        // $('.panel').addClass('visibility');
        $('.accordion-headline:before').toggleClass('rotate');
        dataLayer.push({
          event: 'Close FAQ',
          Question: txt
        });
      } else {
        $('.accordion').next($('.panel')).removeClass('visibility');

        // $('.panel').removeClass('visibility');
        $('.accordion-headline:before').toggleClass('rotate');
        dataLayer.push({
          event: 'Expand FAQ',
          Question: txt
        });
      }
    };
  }

  handleReadMoreClick () {
    window.onclick = e => {
      const btnRead = $(e.target.parentNode).text().substring(0, 50) + '...';
      dataLayer.push({
        event: 'Read More FAQ',
        Question: btnRead
      });
    };
  }

  componentDidMount () {
    this._isMounted = true;
    return () => {
      _isMounted = false;
    };


  }

  componentWillUnmount () {
    this._isMounted = false;
    console.log('component Did UnMount OK');
    console.log(this._isMounted);
    this.handleCollapseClick();
    this.handleReadMoreClick();
  }

  onChangeContent () {
    console.log('onChangeContent OK');

    this.handleCollapseClick();
    this.handleReadMoreClick();
  }

  render () {
    const {
      accordion_title,
      accordion_description,
      // accordion_rows,
      // accordion_id,
      accordion_headline,
      accordion_text,
      accordion_btn_text,
      accordion_btn_url,
      button_link_new_tab
    } = this.props;
    // const accordions = this.state.accordions;

    {/* // Toggle panels accordion - bug event opens every second block on click ?!?!
    const acc = document.getElementsByClassName('accordion');

    for (let p = 0; p < acc.length; p++) {
      acc[p].addEventListener('click', function () {
        this.classList.toggle('active');
        const panel = this.nextElementSibling;
        if (panel.style.display === 'block') {
          panel.style.display = 'none';
          window.onclick = e => {
            const txt = $(e.target).text().substring(0, 50) + '...';
            $('.accordion-headline:before').removeClass('rotate');
            dataLayer.push({
              event: 'Close FAQ',
              Question: txt
            });
          };
        } else {
          panel.style.display = 'block';
          window.onclick = e => {
            const txt = $(e.target).text().substring(0, 50) + '...';
            $('.accordion-headline:before').addClass('rotate');
            dataLayer.push({
              event: 'Expand FAQ',
              Question: txt
            });
          };
        }
      });
    }
  */}

    return (

      <Fragment>

        <section className="block accordion-block my-0 py-0">
          <header>
            {accordion_title &&
            <h2 className="page-section-header">{accordion_title}</h2>
            }
          </header>
          {accordion_description &&
            <p className="page-section-description" dangerouslySetInnerHTML={{ __html: accordion_description }} />
          }

          {/* {accordions.map(index => */}
          <div className="accordion-content my-0 py-0">
            <div className="accordion"
              //onClick={this.handleCollapseClick(index)}
              onClick={this.handleCollapseClick}
            >
              {/* {this.state.isToggleOn ? <p>true</p> : <p>false</p>} */}
              {accordion_headline &&
                <h4 className="accordion-headline" name={accordion_headline}>{accordion_headline}</h4>
              }
            </div>
            <div className="panel visibility"
              // key={index}
            >
              {accordion_text &&
                <p className="accordion-text" dangerouslySetInnerHTML={{ __html: accordion_text }} />
              }
              {accordion_btn_text &&
                <a className="btn btn-secondary btn-accordion"
                  onClick={this.handleReadMoreClick}
                  href={accordion_btn_url}
                  target={ button_link_new_tab ? '_blank' : '' }
                >
                  {accordion_btn_text}
                </a>
              }
            </div>
          </div>
          {/* )
          } */}
        </section>
      </Fragment>
    );
  }
}
