import { createStore } from 'redux';
import reducer from './reducer';

const configureStore = ( initialState ) => {
	return createStore(
		reducer,
		initialState,
		window.devToolsExtension ? window.devToolsExtension() : f => f
	);
};

export default configureStore;
