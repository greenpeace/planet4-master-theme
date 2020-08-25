export const addScriptTag = ({
	src,
	async = true,
	onLoad,
	onError
}) => {
	const script = document.createElement('script');
	script.src = src;
	script.async = async;
	script.addEventListener('load', onLoad);
	script.addEventListener('error', event => {
		script.remove();
		onError(event);
	});

	document.body.appendChild(script);

	return script;
}

