import settings from '../settings';

const fetchApi = ( action, id, callback ) => {
	const idString = id ? '&id=' + id : '';
	const url = settings.apiUrl + '?action=' + action + idString;
	let isError = false;
	fetch( url, {
		credentials: 'same-origin',
	} ).then( res => {
		if ( 200 > res.status || 300 < res.status ) {
			isError = true;
		}

		return res.json();
	} ).then( json => {
		if ( ! isError ) {
			return callback( null, json );
		}

		const error = new Error( 'Server Error' );
		error.text = json.error;
		callback( error );
	} ).catch( error => {
		callback( error );
	} );
};

export default fetchApi;
