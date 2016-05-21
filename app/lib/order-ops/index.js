import api from '../api';

const openInSift = ( id ) => {
	window.open( 'https://siftscience.com/console/users/' + id );
};

const handleApiResponse = ( actions, error, data ) => {
	if ( error ) {
		return actions.setError( error );
	}

	if ( data ) {
		console.log( 'setting data', data );
		actions.setScore( data.score );
		actions.setLabel( data.label );
	}

	actions.isWorking( false );
};

const setLabel = ( id, value, actions ) => {
	actions.isWorking( true );
	let action = 'unset';
	if ( 'bad' === value ) {
		action = 'set_bad';
	}

	if ( 'good' === value ) {
		action = 'set_good';
	}

	const handler = ( error, data ) => handleApiResponse( actions, error, data );
	api( action, id, handler );
};

const backfill = ( id, actions ) => {
	actions.isWorking( true );
	const handler = ( error, data ) => handleApiResponse( actions, error, data );
	api( 'backfill', id, handler );
};

const getLabel = ( id, actions ) => {
	actions.isWorking( true );
	const handler = ( error, data ) => handleApiResponse( actions, error, data );
	api( 'get', id, handler );
};

export default {
	openInSift,
	setLabel,
	backfill,
	getLabel,
};
