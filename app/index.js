import React from 'react';
import ReactDOM from 'react-dom';
import BatchUpload from './containers/batch-upload';
import OrderControl from './containers/order-control';
import { Provider } from 'react-redux'
import state from './state';
import orderOps from './lib/order-ops';

const store = state.init();

const batchElement = document.getElementById( 'batch-upload' );
if ( batchElement ) {
	const update = ( value ) => store.dispatch( state.actions.updateBatch( value ) );
	update( {
		isWorking: true,
		backfilled: [],
		notBackfilled: [],
	} );

	orderOps.orderStats( ( error, data ) => {
		if ( error ) {
			return update( { error } );
		}

		update( data );
	} );

	ReactDOM.render( (
		<Provider store={ store } >
			<BatchUpload />
		</Provider>
	), batchElement );
}

const updateOrder = ( id, value ) => store.dispatch( state.actions.updateOrder( id, value ) );
const orders = [...document.getElementsByClassName( 'siftsci-order' )];
orders && orders.forEach( order => {
	const id = order.attributes['data-id'].value;

	updateOrder( id, { isWorking: true } );
	orderOps.getLabel( id, ( error, data ) => {
		updateOrder( id, { isWorking: false } );
		if ( error ) {
			return updateOrder( id, { error } );
		}

		updateOrder( id, data );
	} );

	ReactDOM.render( (
		<Provider store={ store } >
			<OrderControl orderId={ id } />
		</Provider>
	), document.getElementById( order.id ) );
} );
