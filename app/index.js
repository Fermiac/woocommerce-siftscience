import React from 'react';
import ReactDOM from 'react-dom';
import BatchUpload from './components/batch-upload';
import OrderControl from './components/order-control';

const tryMount = ( id, component ) => {
	const element = document.getElementById( id );
	if ( element ) {
		ReactDOM.render( component, element );
	}
};

tryMount( 'batch-upload', ( <BatchUpload /> ) );

const orders = [...document.getElementsByClassName( 'siftsci-order' )];
const data = window._siftsci_app_input_data ? window._siftsci_app_input_data : {};
const imgPath = data.imgPath;

const noop = () => {};
imgPath && orders && orders.forEach( order => {
	const props = {
		status: 'good',
		imgPath,
		openSiftSci: noop,
		setGood: noop,
		setBad: noop,
		uploadOrder: noop,
	};
	tryMount( order.id, ( <OrderControl { ...props } /> ) );
} );
