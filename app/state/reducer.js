import actionTypes from './action-types';

const actionMap = {};

actionMap[actionTypes.IS_FETCHING] = ( action, state ) => {
	return Object.assign( {}, state, { isFetching: true } );
};

actionMap[actionTypes.FETCH_COMPLETE] = ( action, state ) => {
	return Object.assign( {}, state, {
		isFetching: false,
		summary: action.summary,
	} );
};

const reducer = ( action, state ) => {
	const reducer = actionMap[action.type];
	if ( reducer ) {
		return reducer( action, state );
	}

	return state;
};

export default reducer;
