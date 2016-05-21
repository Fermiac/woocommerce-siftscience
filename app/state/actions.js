import actionTypes from './action-types';

const isWorking = ( value ) => {
	return {
		type: actionTypes.IS_WORKING,
		isWorking: value,
	};
};

const setScore = ( score ) => {
	return {
		type: actionTypes.SET_SCORE,
		score,
	};
};

const setError = ( error ) => {
	return {
		type: actionTypes.SET_ERROR,
		error,
	};
};

const setLabel = ( label ) => {
	return {
		type: actionTypes.SET_LABEL,
		label,
	};
};

export default {
	isWorking,
	setScore,
	setLabel,
	setError,
};
