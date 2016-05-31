import actionTypes from './action-types';

const updateOrder = ( id, value ) => {
	return {
		type: actionTypes.UPDATE_ORDER,
		id, value,
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
	updateBatch,
};
