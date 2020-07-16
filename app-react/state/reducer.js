import actionTypes from './action-types';

const updateOrder = ( state, action ) => {
	const oldOrder = ( state.orders && state.orders[action.id] ) ? state.orders[action.id] : {};
	const newOrder = Object.assign( {}, oldOrder, action.value );
	const newOrders = Object.assign( {}, state.orders, { [action.id]: newOrder } );
	return Object.assign( {}, state, { orders: newOrders } );
};

const deleteOrder = ( state, action ) => {
	const id = action.id;
	if ( ! state.orders[id] ) {
		return state;
	}

	const newOrders = Object.assign( {}, state.orders );
	delete newOrders[id];
	return Object.assign( {}, state, { orders: newOrders } );
};

const updateUser = ( state, action ) => {
	const oldUser = ( state.users && state.users[action.id] ) ? state.users[action.id] : {};
	const newUser = Object.assign( {}, oldUser, action.value );
	const newUsers = Object.assign( {}, state.users, { [action.id]: newUser } );
	return Object.assign( {}, state, { users: newUsers } );
};

const deleteUser = ( state, action ) => {
	const id = action.id;
	if ( ! state.users[id] ) {
		return state;
	}

	const newUsers = Object.assign( {}, state.users );
	delete newUsers[id];
	return Object.assign( {}, state, { users: newUsers } );
};

const updateBatch = ( state, action ) => {
	const oldBatch = state.batch || {};
	const newBatch = Object.assign( {}, oldBatch, action.value );
	return Object.assign( {}, state, { batch: newBatch } );
};

const actionMap = {
	[actionTypes.UPDATE_ORDER]: updateOrder,
	[actionTypes.UPDATE_ORDER]: updateOrder,
	[actionTypes.DELETE_ORDER]: deleteOrder,
	[actionTypes.UPDATE_USER]: updateUser,
	[actionTypes.DELETE_USER]: deleteUser,
	[actionTypes.UPDATE_BATCH]: updateBatch,
};

const reducer = ( state, action ) => {
	const method = actionMap[action.type];
	if ( method ) {
		state = method( state, action );
	}

	return state;
};

export default reducer;
