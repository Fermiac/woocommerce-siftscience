import React from 'react';
import ReactDOM from 'react-dom';
import BatchUpload from './components/batch-upload';
import OrderControl from './components/order-control';
import api from './lib/api';
import { Provider } from 'react-redux'
import state from './state';

const tryMount = ( id, component ) => {
	const element = document.getElementById( id );
	if ( element ) {
		ReactDOM.render( component, element );
	}
};

tryMount( 'batch-upload', (
	<BatchUpload />
) );

const orders = [...document.getElementsByClassName( 'siftsci-order' )];

orders && orders.forEach( order => {
	const store = state( { isWorking: true } );
	const id = order.attributes['data-id'].value;

	tryMount( order.id, (
		<Provider store={ store } >
			<OrderControl orderId={ id } />
		</Provider>
	) );

	api.fetchApi( 'score', id, ( error, data ) => {
		console.log( 'error_' + order.id, error );
		console.log( 'data_' + order.id, data );
	} )
} );
