import { React, Component } from 'react';

export class Preview extends Component {
	render() {
		return <div className={ 'Preview ' + (this.props.isSelected ? 'FloatingPreview' : '') }>
			{
				this.props.showBar
				? <div className='PreviewBar'>Preview</div>
				: null
			}
			<div className='PreviewContainer' >
				{ this.props.children }
			</div>
		</div>
	}
}