import api from '../api';

const openInSift = ( id ) => {
	window.open( 'https://siftscience.com/console/users/' + id );
};

const handleApiResponse = ( updateOrder, error, data ) => {
	updateOrder( {
		error,
		isWorking: false,
	} );

	if ( data ) {
		updateOrder( {
			score: data.score,
			label: data.label,
		} );
	}
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
	api( 'score', id, handler );
};

const initOrder = ( updateOrder ) => {
	updateOrder( {
		isWorking: true,
	} );
};

const orderStats = ( updateBatch ) => {
	updateBatch( { isWorking: true } );
	api( 'order_stats', null, ( error, data ) => {
		updateBatch( {
			error,
			isWorking: false,
		} );

		if ( data ) {
			console.log( 'stats', data );
			updateBatch( data );
		}
	} );
};

export default {
	openInSift,
	setLabel,
	backfill,
	getLabel,
	orderStats,
	initOrder,
};
