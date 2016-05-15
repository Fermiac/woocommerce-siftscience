import React, { PropTypes } from 'react';

const divStyle = {
	width: '24px',
	display: 'block',
	float: 'none',
};

const Icon = ( { imgUrl, alt, text, onClick } ) => {
	return (
		<div id="siftsci_icon" className="siftsci_icon" style={ divStyle } onClick={ onClick } >
			<span style={ { display: 'block' } } className="tips" data-tip={ text }>
				<img src={ imgUrl } alt={ alt } width="20px" height="20px" />
			</span>
		</div>
	);
};

const scoreStyle = {
	color: 'white',
	textAlign: 'center',
	border: '1px solid black',
	width: '20px',
	height: '20px',
	margin: '0px',
};

const getColor = ( score ) => {
	if ( 90 < score ) {
		return 'green';
	}

	if ( 50 < score ) {
		return 'orange';
	}

	return 'red';
};

const Score = ( { score, onClick } ) => {
	const style = Object.assign( {}, scoreStyle, {
		backgroundColor: getColor( score ),
	} );

	return (
		<div id="siftsci_score" className="siftsci_score" style={ divStyle } onClick={ onClick }>
			<span style={ { display: 'block' } } className="tips" data-tip="click to view details in SiftScience">
				<div style={ style }>{ score }</div>
			</span>
		</div>
	);
};

const LabelButton = ( { type, label, imgPath, setLabel } ) => {
	const isSet = type === label
	const image = type + ( isSet ? '-gray.png' : '.png' );
	const callback = () => setLabel( isSet ? null : type );
	return (
		<Icon imgUrl={ imgPath + image } alt={ type } onClick={ callback } />
	);
};

const control = ( props ) => {
	if ( props.error ) {
		return <Icon imgUrl={ props.imgPath + 'error.png' } alt="error" text={ props.error }/>;
	}

	if ( props.isWorking ) {
		return <Icon imgUrl={ props.imgPath + 'spinner.gif' } alt="working" text="working..."/>;
	}

	if ( null !== props.score ) {
		return (
			<div>
				<Score score={ props.score } onClick={ props.openSiftSci } />
				<LabelButton { ...props } type="good" />
				<LabelButton { ...props } type="bad" />
			</div>
		);
	}

	return <Icon imgUrl={ props.imgPath + 'upload.png' } alt="upload" onClick={ props.uploadOrder } />;
};

control.propTypes = {
	score: PropTypes.number,
	label: PropTypes.string,
	imgPath: PropTypes.string.isRequired,
	openSiftSci: PropTypes.func.isRequired,
	setLabel: PropTypes.func.isRequired,
	uploadOrder: PropTypes.func.isRequired,
};

export default control;
