import React, { PropTypes } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import OrderControl from '../../components/order-control';
import reduxActions from '../../state/actions';
import settings from '../../lib/settings';
import orderOps from '../../lib/order-ops';

const container = ( { orderId, state, actions } ) => {
	const props = {
		imgPath: settings.imgPath,
		openSiftSci: () => orderOps.openSiftSci( orderId ),
		setLabel: ( value ) => orderOps.setLabel( orderId, value, actions ),
		uploadOrder: () => orderOps.backfill( orderId, actions ),
		isWorking: state.isWorking,
		score: state.score,
		label: state.label,
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
		actions: bindActionCreators( reduxActions, dispatch ),
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( container );
