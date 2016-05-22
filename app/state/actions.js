import actionTypes from './action-types';

const updateOrder = ( id, value ) => {
	return {
		type: actionTypes.UPDATE_ORDER,
		id, value,
	};
};

export default {
	updateOrder,
};
