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

export default {
	openInSift,
	setLabel,
	backfill,
};
