import React from 'react';
import ReactDOM from 'react-dom';
import BatchUpload from './components/batch-upload';
import OrderControl from './containers/order-control';
import { Provider } from 'react-redux'
import state from './state';
import api from './lib/api';

const store = state.init();

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
	const id = order.attributes['data-id'].value;

	store.dispatch( state.actions.updateOrder( id, { isWorking: true } ) );
	api( 'score', id, ( error, data ) => {
		if ( data ) {
			store.dispatch( state.actions.updateOrder( id, { label: data.label } ) );
			store.dispatch( state.actions.updateOrder( id, { score: data.score } ) );
		}

		store.dispatch( state.actions.updateOrder( id, { isWorking: false } ) );
	} );

	tryMount( order.id, (
		<Provider store={ store } >
			<OrderControl orderId={ id } />
		</Provider>
	) );
} );
