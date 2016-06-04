import React, { PropTypes } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import BatchUpload from '../../components/batch-upload';
import appState from '../../state';
import orderOps from '../../lib/order-ops';

const container = ( { state, updateBatch } ) => {
	const refresh = () => {
		orderOps.orderStats( updateBatch );
	};

	const backfill = () => {
		orderOps.orderStats( updateBatch )
	};

	const clearAll = () => {
		orderOps.clearAll( ( error ) => {
			if ( error ) {
				return updateBatch( { error } );
				refresh();
			}
		} );
	};

	return (
		<BatchUpload
			error={ state.error }
			backfilledOrders={ state.backfilled }
			notBackfilledOrders={ state.notBackfilled }
			refresh={ refresh }
			backfill={ backfill }
			clearAll={ clearAll }
		/>
	);
};

container.propTypes = {
	state: PropTypes.object.isRequired,
	updateBatch: PropTypes.func.isRequired,
};

function mapStateToProps( state ) {
	return {
		state: state.batch,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		updateBatch: bindActionCreators( appState.actions, dispatch ).updateBatch,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( container );
