import React, { PropTypes } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import BatchUpload from '../../components/batch-upload';
import appState from '../../state';
import orderOps from '../../lib/order-ops';
import Async from 'async';

const container = ( { state, updateBatch } ) => {
	const handleResult = ( error, data ) => {
		if ( error ) {
			return updateBatch( { error } );
		}

		updateBatch( data );
	};

	const refresh = () => orderOps.orderStats( handleResult );
	const clearAll = () => orderOps.clearAll( handleResult );

	const backfill = ( id, callback ) => orderOps.backfill( id, callback );
	const backfillAll = () => {
		Async.eachSeries( state.notBackfilled, backfill, ( error ) => {
			if ( error ) {
				return updateBatch( { error } );
			}

			refresh();
		} )
	};

	return (
		<BatchUpload
			error={ state.error }
			backfilledOrders={ state.backfilled }
			notBackfilledOrders={ state.notBackfilled }
			refresh={ refresh }
			backfill={ backfillAll }
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
