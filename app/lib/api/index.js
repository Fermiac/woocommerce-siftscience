import settings from '../settings';

const fetchApi = ( action, id, callback ) => {
	const idString = id ? '&id=' + id : '';
	const url = settings.apiUrl + '?action=' + action + idString;
	let text = null;
	fetch( url, { credentials: 'same-origin' } )
		.then( ( res ) => {
			if ( 200 > res.status || 300 < res.status ) {
				return res.text()
					.then( ( textResult ) => {
						text = textResult;
						const error = new Error( 'Server Error' );
						error.text = textResult;
						throw error;
					} )
			}

			return res.text();
		} )
		.then( ( textResult ) => {
			text = textResult;
			return JSON.parse( text )
		} )
		.then( ( json ) => callback( null, json ) )
		.catch( ( error ) => {
			console.log( 'error parsing api result' );
			console.log( error );
			console.log( 'api returned: ' );
			console.log( text );
			callback( error );
		} );
};

export default fetchApi;
