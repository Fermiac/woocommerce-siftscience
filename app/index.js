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
let imgPath = '';
if ( _siftsci_app_input_data ) {
	imgPath = _siftsci_app_input_data.imgPath;
}

orders && orders.forEach( order => {
	const noop = () => {};
	const props = {
		status: '',
		imgPath,
		openSiftSci: noop,
		setGood: noop,
		setBad: noop,
		uploadOrder: noop,
	};
	tryMount( order.id, ( <OrderControl { ...props } /> ) );
} );
