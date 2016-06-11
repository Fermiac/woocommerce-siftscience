import api from '../api';

const openInSift = ( id ) => {
	window.open( 'https://siftscience.com/console/users/' + id );
};

const setLabel = ( id, value, callback ) => {
	let action = 'unset';
	if ( 'bad' === value ) {
		action = 'set_bad';
	}

	if ( 'good' === value ) {
		action = 'set_good';
	}

	api( action, id, callback );
};

const backfill = ( id, callback ) => {
	api( 'backfill', id, callback );
};

const getLabel = ( id, callback ) => {
	api( 'score', id, callback );
};

const orderStats = ( callback ) => {
	api( 'order_stats', null, callback );
};

const clearAll = ( callback ) => {
	api( 'clear_all', null, callback );
};

const getUserData = ( sift ) => {
	if ( ! sift ) {
		return null;
	};

	const result = {};

	if ( sift.score ) {
		result.score = Math.round( sift.score * 100 );
	}

	result.label = 'none';
	if ( sift.latest_label ) {
		result.label = sift.latest_label.is_bad ? 'bad' : 'good';
	}

	return result;
};

export default {
	openInSift,
	setLabel,
	backfill,
	getLabel,
	orderStats,
	clearAll,
	getUserData,
};
