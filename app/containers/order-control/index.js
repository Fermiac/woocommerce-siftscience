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

	const props = {
		imgPath: settings.imgPath,
		openSiftSci: () => orderOps.openSiftSci( orderId ),
		setLabel: ( value ) => orderOps.setLabel( orderId, value, updateOrder ),
		uploadOrder: () => orderOps.backfill( orderId, updateOrder ),
		isWorking: order.isWorking,
		score: order.score,
		label: order.label,
	};

	return (
		<OrderControl { ...props } />
	);
};

container.propTypes = {
	orderId: PropTypes.string.isRequired,
	state: PropTypes.object.isRequired,
	actions: PropTypes.object.isRequired,
};

function mapStateToProps( state ) {
	return {
		state: state,
	};
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
