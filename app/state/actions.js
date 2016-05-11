import actionTypes from './action-types';

const isFetching = () => {
	return {
		type: actionTypes.IS_FETCHING,
	};
};

const fetchComplete = ( summary ) => {
	return {
		type: actionTypes.FETCH_COMPLETE,
		summary,
	};
};

export default {
	isFetching,
	fetchComplete,
};  
