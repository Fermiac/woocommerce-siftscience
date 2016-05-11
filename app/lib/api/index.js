//import fetch from 'fetch';

const fetchRoot = () => {
	fetch( 'http://localhost/ss/wp-content/plugins/woocommerce-siftscience/api.php', {
		credentials: 'same-origin',
	} )
		.then( res => res.json() )
		.then( json => console.log( 'api', json ) );
};

export default fetchRoot;
