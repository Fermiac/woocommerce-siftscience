import settings from '../settings';

const fetchApi = ( action, id, callback ) => {
	const url = settings.apiUrl + '?action=' + action + '&id=' + id;
	console.log( 'fetch url', url );
	let isError = false;
	fetch( url, {
		credentials: 'same-origin',
	} ).then( res => {
		if ( 200 > res.status || 300 < res.statys ) {
			isError = true;
		}

		return res.json();
	} ).then( json => {
		if ( ! isError ) {
			console.log( 'fetch json: ', json );
			return callback( null, json );
		}

		const error = new Error( 'Server Error' );
		error.text = json.error;
		console.log( 'fetch error: ', error );
		callback( error );
	} ).catch( error => {
		console.log( 'fetch error2: ', error );
		callback( error );
	} );
};

export default fetchApi;
