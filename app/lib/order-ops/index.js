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
	}

	const result = {};

	if ( sift.scores && sift.scores.payment_abuse ) {
		result.score = Math.round( sift.scores.payment_abuse.score * 100 );
	}

	result.label = 'none';
	if ( sift.latest_labels && sift.latest_labels.payment_abuse ) {
		result.label = sift.latest_labels.payment_abuse.is_bad ? 'bad' : 'good';
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
