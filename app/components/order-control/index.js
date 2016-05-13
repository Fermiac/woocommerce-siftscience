import React, { PropTypes } from 'react';

const divStyle = {
	width: '24px',
	display: 'block',
	float: 'none',
};

const Icon = ( { imgUrl, alt, onClick } ) => {
	return (
		<div style={ divStyle } onClick={ onClick } >
			<span style={ { display: 'block' } } class="tips">
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

const Score = ( { score, color, onClick } ) => {
	const style = Object.assign( {}, scoreStyle, {
		backgroundColor: color,
	} );

	return (
		<div style={ divStyle } onClick={ onClick }>
			<span style={ { display: 'block' } } className="tips">
				<div style={ style }>{ score }</div>
			</span>
		</div>
	);
};

const control = ( { status, imgPath, openSiftSci, setGood, setBad, uploadOrder } ) => {
	switch ( status ) {
		case 'neutral':
			return (
				<div>
					<Score score="76" color="red" onClick={ openSiftSci } />
					<Icon imgUrl={ imgPath + 'good-gray.png' } alt="good" onClick={ setGood } />
					<Icon imgUrl={ imgPath + 'bad-gray.png' } alt="bad" onClick={ setBad } />
				</div>
			);
		case 'good':
			return (
				<div>
					<Score score="76" color="red" onClick={ openSiftSci } />
					<Icon imgUrl={ imgPath + 'good.png' } alt="good" onClick={ setGood } />
					<Icon imgUrl={ imgPath + 'bad-gray.png' } alt="bad" onClick={ setBad } />
				</div>
			);
		case 'bad':
			return (
				<div>
					<Score score="76" color="red" onClick={ openSiftSci } />
					<Icon imgUrl={ imgPath + 'good-gray.png' } alt="good" onClick={ setGood } />
					<Icon imgUrl={ imgPath + 'bad.png' } alt="bad" onClick={ setBad } />
				</div>
			);
		case 'upload':
			return <Icon imgUrl={ imgPath + 'upload.png' } alt="upload" onClick={ uploadOrder } />;
		case 'error':
			return 	<Icon imgUrl={ imgPath + 'error.png' } alt="error"/>;
		default:
			return 	<Icon imgUrl={ imgPath + 'spinner.gif' } alt="working"/>;
	};
};

control.propTypes = {
	status: PropTypes.string.isRequired,
	imgPath: PropTypes.string.isRequired,
	openSiftSci: PropTypes.func.isRequired,
	setGood: PropTypes.func.isRequired,
	setBad: PropTypes.func.isRequired,
	uploadOrder: PropTypes.func.isRequired,
};

export default control;
