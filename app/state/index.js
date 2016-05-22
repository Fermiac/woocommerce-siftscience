import { createStore } from 'redux';
import reducer from './reducer';
import actions from './actions';
import actionTypes from './action-types';

const init = () => {
	return createStore( reducer, {} );
};

export default {
	init,
	reducer,
	actions,
	actionTypes,
};
