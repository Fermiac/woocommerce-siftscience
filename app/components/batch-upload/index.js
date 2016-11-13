import React, { PropTypes } from 'react';

const StatView = ( { backfilled, notBackfilled } ) => {
	return (
		<p>
			Orders: { notBackfilled.length + backfilled.length } <br />
			Backfilled: { backfilled.length } <br />
			Not Backfilled: { notBackfilled.length }
		</p>
	);
};

StatView.propTypes = {
	backfilled: PropTypes.array.isRequired,
	notBackfilled: PropTypes.array.isRequired,
};

const View = ( props ) => {
	const { error, status, orderId } = props;

	if ( error ) {
		return <p>{ error.toString() }:{ error.text }</p>;
	}

	switch ( status ) {
		case 'loading':
			return <p>Loading...</p>;
		case 'stats':
			return <StatView { ...props } />;
		case 'backfill':
			return <p>Backfilling order #{ orderId }</p>
		default:
			return <p>Error: unknown status [ { status } ]</p>
	}
};

View.propTypes = {
	error: PropTypes.object,
	status: PropTypes.string.isRequired,
	orderId: PropTypes.number,
};

const component = ( props ) => {
	const { clearAll, backfill, refresh } = props;
	return (
		<div>
			<button type="button" className="button-primary" onClick={ clearAll } >
				Clear Data
			</button>
			<button type="button" className="button-primary" onClick={ backfill }>
				Back-Fill
			</button>
			<button type="button" className="button-primary" onClick={ refresh }>
				Refresh
			</button>
			<View { ...props }/>
		</div>
	);
};

component.propTypes = {
	refresh: PropTypes.func.isRequired,
	backfill: PropTypes.func.isRequired,
	clearAll: PropTypes.func.isRequired,
};

export default component;
