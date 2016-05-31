import React, { PropTypes } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import BatchUpload from '../../components/batch-upload';
import appState from '../../state';

const container = ( { state, updateBatch } ) => {
	return (
		<BatchUpload
			totalOrders={ state.total }
			backfilledOrders={ state.backfilled }
			isWorking={ state.isWorking }
			update={ updateBatch }
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
