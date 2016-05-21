import { createStore } from 'redux';
import reducer from './reducer';

const configureStore = () => {
	const store = createStore( reducer, {} );

	store.dispatch( {
		type: 'IS_WORKING',
		isWorking: true,
	} );

	return store;
};

export default configureStore;
