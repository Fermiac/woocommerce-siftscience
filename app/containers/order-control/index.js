import React, { PropTypes } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import OrderControl from '../../components/order-control';
import settings from '../../lib/settings';
import orderOps from '../../lib/order-ops';
import appState from '../../state';

const container = ( { orderId, state, actions } ) => {
	const order = state.orders[orderId];
	const updateOrder = value => actions.updateOrder( orderId, value );
	const openSiftSci = () => {
		orderOps.openInSift( orderId );
	};

	const handleResponse = ( error, data ) => {
		updateOrder( { isWorking: false } );
		if ( error ) {
			return updateOrder( { error } );
		}

		updateOrder( data );
	};

	const uploadOrder = () => {
		updateOrder( { isWorking: true } );
		orderOps.backfill( orderId, handleResponse );
	};

	const setLabel = ( value ) => {
		updateOrder( { isWorking: true } );
		orderOps.setLabel( orderId, value, handleResponse );
	};

	return <OrderControl { ...settings } { ...order } { ...{ openSiftSci, setLabel, uploadOrder } } />;
};

container.propTypes = {
	orderId: PropTypes.string.isRequired,
	state: PropTypes.object.isRequired,
	actions: PropTypes.object.isRequired,
};

function mapStateToProps( state ) {
	return { state };
}

function mapDispatchToProps( dispatch ) {
	return {
		actions: bindActionCreators( appState.actions, dispatch ),
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( container );
