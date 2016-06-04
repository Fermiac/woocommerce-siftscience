import React, { PropTypes } from 'react';
import api from '../../lib/api';

const component = ( { backfilledOrders, notBackfilledOrders } ) => {
	const backfilled = backfilledOrders.length;
	const notBackfilled = notBackfilledOrders.length;
	const total = backfilled + notBackfilled;
	return (
		<div>
			<button
				type="button"
				className="button-primary"
				style={ { backgroundColor: 'green' } }
				onClick={ api }
			>
				Upload
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

			<p class="description">
				Send all your orders to SiftScience
			</p>
		</div>
	);
};

component.propTypes = {
	backfilledOrders: PropTypes.array.isRequired,
	notBackfilledOrders: PropTypes.array.isRequired,
	isWorking: PropTypes.bool.isRequired,
	update: PropTypes.func.isRequired,
};

export default component;
