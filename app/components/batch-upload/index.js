import React, { PropTypes } from 'react';
import api from '../../lib/api';

const component = ( { totalOrders, backfilledOrders } ) => {
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
				Orders: { totalOrders }
			</p>

			<p>
				Backfilled: { backfilledOrders }
			</p>

			<p>
				Not Backfilled: { totalOrders - backfilledOrders }
			</p>

			<p class="description">
				Send all your orders to SiftScience
			</p>
		</div>
	);
};

component.propTypes = {
	totalOrders: PropTypes.number.isRequired,
	backfilledOrders: PropTypes.number.isRequired,
	isWorking: PropTypes.bool.isRequired,
	update: PropTypes.func.isRequired,
};

export default component;
