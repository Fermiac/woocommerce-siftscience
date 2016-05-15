import React from 'react';
import { connect } from 'react-redux';
import OrderControl from '../../components/order-control';
import actions from '../../state/actions';
import settings from '../../lib/settings';
const orderOps = {};

const setLabel = ( orderId, value ) => {

}

const container = ( { orderId, isWorking, error, score, label, setLabel, setScore, setError } ) => {
	const props = {
		score, label, error, isWorking,
		imgPath: settings.imgPath,
		openSiftSci: () => orderOps.openInSift( orderId ),
		setLabel: ( value ) => {
			orderOps.setLabel( orderId, value, ( error, data ) => {
				if ( error ) {
					return setError( error );
				}

				setScore( data.score );
				setLabel( data.label );
			} );
		},
		uploadOrder: () => {
			orderOps.backfill( orderId );
		},
	};

	return (
		<OrderControl { ...props } />
	);
};

function mapStateToProps( state ) {
	return state;
}

function mapDispatchToProps( dispatch ) {
	return bindActionCreators( actions, dispatch );
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( container );
