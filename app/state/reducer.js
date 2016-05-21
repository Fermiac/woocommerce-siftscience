import actionTypes from './action-types';

const actionMap = {};

actionMap[actionTypes.IS_WORKING] = ( state, action ) => {
	return Object.assign( {}, state, { isWorking: action.isWorking } );
};

actionMap[actionTypes.SET_SCORE] = ( state, action ) => {
	return Object.assign( {}, state, { score: action.score } );
};

actionMap[actionTypes.SET_LABEL] = ( state, action ) => {
	return Object.assign( {}, state, { label: action.label } );
};

actionMap[actionTypes.SET_ERROR] = ( state, action ) => {
	return Object.assign( {}, state, { error: action.error } );
};

const reducer = ( state = {}, action ) => {
	const method = actionMap[action.type];
	if ( method ) {
		state = method( state, action );
	}

	console.log( 'state', state );
	return state;
};

export default reducer;
