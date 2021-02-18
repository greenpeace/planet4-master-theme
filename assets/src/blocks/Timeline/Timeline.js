import { useScript } from '../../components/useScript/useScript';
import { useStyleSheet } from '../../components/useStyleSheet/useStyleSheet';
import { useRef, useEffect } from 'react';
import { uniqueId } from 'lodash';

const TIMELINE_JS_VERSION = '3.8.10';

export const Timeline = (props) => {
	const {
		google_sheets_url,
		timenav_position,
		start_at_end,
		language
	} = props;

	const timelineNode = useRef(null);

	const [stylesLoaded, stylesError] = useStyleSheet(
    `https://cdn.knightlab.com/libs/timeline3/${TIMELINE_JS_VERSION}/css/timeline.css`
	);

  const setupTimeline = function() {
		timelineNode.current.id = uniqueId('timeline');

		new TL.Timeline(timelineNode.current.id, google_sheets_url, {
			'timenav_position': timenav_position,
			'start_at_end': start_at_end,
			'language': language
		});
	}

	// Revert TimelineJS global usage of lodash,
	// as it conflicts with Wordpress underscore lib
	// see https://jira.greenpeace.org/browse/PLANET-5960
	const revertLodash = function () {
		_.noConflict();
	}

	const [scriptLoaded, scriptError] = useScript(
    `https://cdn.knightlab.com/libs/timeline3/${TIMELINE_JS_VERSION}/js/timeline-min.js`,
    revertLodash
	);

	useEffect(
		() => {
			if (stylesLoaded && scriptLoaded) {
				setupTimeline();
			}
		},
		[
			stylesLoaded,
			scriptLoaded,
			start_at_end,
			google_sheets_url,
			timenav_position,
			language,
		],
	);

	return <div ref={ timelineNode }></div>
}
