import api from '../api';

const openInSift = ( user ) => {
	window.open( 'https://siftscience.com/console/users/' + user );
};

const setLabel = ( id, value ) => {
	let action = 'unset';
	if ( 'bad' === value ) {
		action = 'set_bad';
	}

	if ( 'good' === value ) {
		action = 'set_good';
	}

	return api( action, id );
};

const backfill = ( id ) => {
	return api( 'backfill', id );
};

const getLabel = ( id ) => {
	return api( 'score', id );
};

const orderStats = () => {
	return api( 'order_stats', null );
};

const clearAll = () => {
	return api( 'clear_all', null );
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
