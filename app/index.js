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
		status: 'loading',
		backfilled: [],
		notBackfilled: [],
	} );

	orderOps.orderStats( ( error, data ) => {
		if ( error ) {
			return update( { error } );
		}

		update( Object.assign( {}, data, { status: 'stats' } ) );
	} );

	ReactDOM.render( (
		<Provider store={ store } >
			<BatchUpload />
		</Provider>
	), batchElement );
}

const updateOrder = ( id, value ) => store.dispatch( state.actions.updateOrder( id, value ) );
const updateUser = ( id, value ) => store.dispatch( state.actions.updateUser( id, value ) );
const orders = [...document.getElementsByClassName( 'siftsci-order' )];
orders && orders.forEach( order => {
	const id = order.attributes['data-id'].value;

	updateOrder( id, { isWorking: true } );
	orderOps.getLabel( id, ( error, data ) => {
		updateOrder( id, { isWorking: false } );
		if ( error ) {
			return updateOrder( id, { error: error.toString() } );
		}

		console.log( data );
		if ( id !== data.order_id ) {
			console.log( 'Strange: request order id ' + id + ' but got order_id ' + data.order_id );
		}

		updateOrder( id, {
			userId: data.user_id,
			isBackfilled: data.is_backfilled,
		} );

		updateUser( data.user_id, orderOps.getUserData( data.sift ) );
	} );

	ReactDOM.render( (
		<Provider store={ store } >
			<OrderControl orderId={ id } />
		</Provider>
	), document.getElementById( order.id ) );
} );
