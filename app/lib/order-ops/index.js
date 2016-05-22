import api from '../api';

const openInSift = ( id ) => {
	window.open( 'https://siftscience.com/console/users/' + id );
};

const handleApiResponse = ( updateOrder, error, data ) => {
	if ( error ) {
		return updateOrder( { error } );
	}

	if ( data ) {
		updateOrder( {
			score: data.score,
			label: data.label,
		} );
	}

	updateOrder( {
		error: null,
		isWorking: false,
	} );
};

const setLabel = ( id, value, updateOrder ) => {
	updateOrder( { isWorking: true } );
	let action = 'unset';
	if ( 'bad' === value ) {
		action = 'set_bad';
	}

	if ( 'good' === value ) {
		action = 'set_good';
	}

	const handler = ( error, data ) => handleApiResponse( updateOrder, error, data );
	api( action, id, handler );
};

const backfill = ( id, updateOrder ) => {
	updateOrder( { isWorking: true } );
	const handler = ( error, data ) => handleApiResponse( updateOrder, error, data );
	api( 'backfill', id, handler );
};

const getLabel = ( id, updateOrder ) => {
	updateOrder( { isWorking: true } );
	const handler = ( error, data ) => handleApiResponse( updateOrder, error, data );
	api( 'get', id, handler );
};

export default {
	openInSift,
	setLabel,
	backfill,
	getLabel,
};
