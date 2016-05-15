import actionTypes from './action-types';

const actionMap = {};

actionMap[actionTypes.IS_WORKING] = ( action, state ) => {
	return Object.assign( {}, state, { isWorking: action.isWorking } );
};

actionMap[actionTypes.SET_SCORE] = ( action, state ) => {
	return Object.assign( {}, state, { score: action.score } );
};

actionMap[actionTypes.SET_LABEL] = ( action, state ) => {
	return Object.assign( {}, state, { label: action.label } );
};

actionMap[actionTypes.SET_ERROR] = ( action, state ) => {
	return Object.assign( {}, state, { error: action.error } );
};

const reducer = ( action, state ) => {
	const method = actionMap[action.type];
	if ( method ) {
		return method( action, state );
	}

	return state;
};

export default reducer;
