import { React, Component } from 'react';

export class Preview extends Component {
	constructor(props) {
		super(props);
		this.state = {
			detach: false
		};
		this.detach = this.detach.bind(this);
	}
	detach() {
		this.setState({ detach: !this.state.detach })
	}
	render() {
		return <div className={ 'Preview ' + (this.props.isSelected && this.state.detach ? 'FloatingPreview' : '') }>
			{
				this.props.showBar
				? <div className='PreviewBar'>
						Preview
						<button className='DetachPreview' onClick={this.detach}>
							{
								this.state.detach
								? 'Reattach'
								: 'Detach'
							}
						</button>
					</div>
				: null
			}
			<div className='PreviewContainer' >
				{ this.props.children }
			</div>
		</div>
	}
}