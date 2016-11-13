import settings from '../settings';

const fetchApi = ( action, id, callback ) => {
	const idString = id ? '&id=' + id : '';
	const url = settings.apiUrl + '?action=' + action + idString;
	fetch( url, { credentials: 'same-origin' } )
		.then( ( res ) => {
			if ( 200 > res.status || 300 < res.status ) {
				return res.text()
					.then( ( text ) => {
						const error = new Error( 'Server Error' );
						error.text = text;
						throw error;
					} )
			}

			return res.text();
		} )
		.then( ( text ) => {
			try {
				return JSON.parse( text )
			} catch ( error ) {
				error.text = text;
				throw error;
			}
		} )
		.then( ( json ) => callback( null, json ) )
		.catch( ( error ) => {
			console.log( 'error parsing api result' );
			console.log( error );
			console.log( 'api returned: ' );
			console.log( error.text );
			callback( error );
		} );
};

export default fetchApi;
