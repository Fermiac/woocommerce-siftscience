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

		updateBatch( Object.assign( {}, data, { status: 'stats' } ) );
	};

	const refresh = () => {
		updateBatch( { status: 'loading' } );
		orderOps.orderStats( handleResult );
	};

	const clearAll = () => {
		updateBatch( { status: 'loading' } );
		orderOps.clearAll( handleResult );
	};

	const backfillOrder = ( orderId, callback ) => {
		updateBatch( {
			status: 'backfill',
			orderId,
		} );

		orderOps.backfill( orderId, callback );
	};

	const backfill = () => {
		updateBatch( { status: 'loading' } );
		Async.eachSeries( state.notBackfilled, backfillOrder, ( error ) => {
			if ( error ) {
				return updateBatch( { error } );
			}

			refresh();
		} )
	};

	return <BatchUpload { ...state } { ...{ refresh, clearAll, backfill } }/>;
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
