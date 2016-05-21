import React from 'react';
import ReactDOM from 'react-dom';
import BatchUpload from './components/batch-upload';
import OrderControl from './containers/order-control';
import { Provider } from 'react-redux'
import state from './state';
import actions from './state/actions';
import api from './lib/api';

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
	const store = state();
	const id = order.attributes['data-id'].value;

	api( 'score', id, ( error, data ) => {
		console.log( 'error', error );
		console.log( 'data', data );
		if ( data ) {
			store.dispatch( actions.setLabel( data.label ) );
			store.dispatch( actions.setScore( data.score ) );
		}
		store.dispatch( actions.isWorking( false ) );
	} );

	tryMount( order.id, (
		<Provider store={ store } >
			<OrderControl orderId={ id } />
		</Provider>
	) );
} );
