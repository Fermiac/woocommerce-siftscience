import actionTypes from './action-types';

const actionMap = {};

actionMap[actionTypes.UPDATE_ORDER] = ( state, action ) => {
	const oldOrder = ( state.orders && state.orders[action.id] ) ? state.orders[action.id] : {};
	const newOrder = Object.assign( {}, oldOrder, action.value );
	const newOrders = Object.assign( {}, state.orders, { [action.id]: newOrder } );
	return Object.assign( {}, state, { orders: newOrders } );
};

actionMap[actionTypes.UPDATE_BATCH] = ( state, action ) => {
	const oldBatch = state.batch || {};
	const newBatch = Object.assign( {}, oldBatch, action.value )
	return Object.assign( {}, state, { batch: newBatch } );
};

const reducer = ( state, action ) => {
	const method = actionMap[action.type];
	if ( method ) {
		state = method( state, action );
	}

	return state;
};

export default reducer;
