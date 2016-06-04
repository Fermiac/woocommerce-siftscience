import React, { PropTypes } from 'react';

const component = ( { backfilledOrders, notBackfilledOrders, refresh, backfill, clearAll } ) => {
	const backfilled = backfilledOrders.length;
	const notBackfilled = notBackfilledOrders.length;
	const total = backfilled + notBackfilled;
	return (
		<div>
			<button
				type="button"
				className="button-primary"
				onClick={ clearAll }
			>
				Clear Data
			</button>
			<button
				type="button"
				className="button-primary"
				onClick={ backfill }
			>
				Back-Fill
			</button>
			<button
				type="button"
				className="button-primary"
				onClick={ refresh }
			>
				Refresh
			</button>

			<p>
				Orders: { total }
			</p>

			<p>
				Backfilled: { backfilled }
			</p>

			<p>
				Not Backfilled: { notBackfilled }
			</p>
		</div>
	);
};

component.propTypes = {
	backfilledOrders: PropTypes.array.isRequired,
	notBackfilledOrders: PropTypes.array.isRequired,
	refresh: PropTypes.func.isRequired,
	backfill: PropTypes.func.isRequired,
	clearAll: PropTypes.func.isRequired,
};

export default component;
