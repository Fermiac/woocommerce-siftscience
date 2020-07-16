import React, { PropTypes } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import OrderControl from '../../components/order-control';
import settings from '../../lib/settings';
import orderOps from '../../lib/order-ops';
import appState from '../../state';

const container = ( { orderId, state, actions } ) => {
	const order = state.orders[orderId];
	const user = ( order && order.userId && state.users ) ? state.users[order.userId] : null;
	const updateOrder = value => actions.updateOrder( orderId, value );
	const updateUser = value => actions.updateUser( order.userId, value );
	const openSiftSci = () => {
		orderOps.openInSift( order.userId );
	};

	const handleResponse = ( error, data ) => {
		updateOrder( { isWorking: false } );
		if ( error ) {
			return updateOrder( { error: error.toString() } );
		}

		updateOrder( {
			userId: data.user_id,
			isBackfilled: data.is_backfilled,
		} );

		updateUser( orderOps.getUserData( data.sift ) );
	};

	const uploadOrder = () => {
		updateOrder( { isWorking: true } );
		orderOps.backfill( orderId, handleResponse );
	};

	const setLabel = ( value ) => {
		updateOrder( { isWorking: true } );
		orderOps.setLabel( orderId, value, handleResponse );
	};

	return <OrderControl { ...settings } { ...order } { ...user } { ...{ openSiftSci, setLabel, uploadOrder } } />;
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
