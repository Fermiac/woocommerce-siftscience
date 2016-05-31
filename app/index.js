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
		total: 0,
		backfilled: 0,
	} );

	orderOps.orderStats( update );

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

	const updateThisOrder = ( value ) => updateOrder( id, value );
	orderOps.initOrder( updateThisOrder );
	orderOps.getLabel( id, updateThisOrder );

	ReactDOM.render( (
		<Provider store={ store } >
			<OrderControl orderId={ id } />
		</Provider>
	), document.getElementById( order.id ) );
} );
