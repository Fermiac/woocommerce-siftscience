//import fetch from 'fetch';

const fetchRoot  = () => {
	fetch( 'http://localhost/ss/wc-api/v3', {
		credentials: 'same-origin'
	} )
		.then( res => res.json() )
		.then( json => console.log( 'api', json ) );
};

export default fetchRoot;
