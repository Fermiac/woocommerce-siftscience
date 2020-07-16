import actionTypes from './action-types';

const updateOrder = ( id, value ) => {
	return {
		type: actionTypes.UPDATE_ORDER,
		id, value,
	};
};

const deleteOrder = ( id ) => {
	return {
		type: actionTypes.DELETE_ORDER,
		id,
	};
};

const updateUser = ( id, value ) => {
	return {
		type: actionTypes.UPDATE_USER,
		id, value,
	};
};

const deleteUser = ( id ) => {
	return {
		type: actionTypes.DELETE_USER,
		id,
	};
};

const updateBatch = ( value ) => {
	return {
		type: actionTypes.UPDATE_BATCH,
		value,
	};
};

export default {
	updateOrder,
	deleteOrder,
	updateUser,
	deleteUser,
	updateBatch,
};
