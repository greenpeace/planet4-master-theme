import {COVERS_LAYOUTS} from "./CoversConstants";

const {__} = wp.i18n;

export const VERSION = 2;

export const attributes = {
	cover_type: {
		type: 'string',
		default: 'content',
	},
	initialRowsLimit: {
		type: 'integer',
		default: 1,
	},
	title: {
		type: 'string',
		default: '',
	},
	description: {
		type: 'string',
		default: '',
	},
	tags: {
		type: 'array',
		default: [],
	},
	post_types: {
		type: 'array',
		default: [],
	},
	posts: {
		type: 'array',
		default: [],
	},
	version: {
		type: 'integer',
		default: VERSION,
	},
	layout: {
		type: 'string',
		default: COVERS_LAYOUTS.grid,
	},
	isExample: {
		type: 'boolean',
		default: false,
	},
	exampleCovers: { // Used for the block's preview, which can't extract items from anything.
		type: 'object',
	},
	readMoreText: {
		type: 'string',
		default: __('Load more', 'planet4-blocks'),
	},
};