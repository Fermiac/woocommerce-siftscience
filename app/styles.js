import {getSettings} from './api'

export const divStyle = {
	color: 'white',
	textAign: 'center',
	border: '1px solid black',
	width: '24px',
	height: '20px',
	margin: '0px',  
	display: 'block',
	float: 'none',
}

export const iconStyle = {
	width: '24px',
	display: 'block',
	float: 'none',
}

export const scoreStyle = {
	color: 'black',
	textAlign: 'center',
	border: '1px solid black',
	width: '20px',
	height: '20px',
	margin: '0px',
	backgroundColor: 'white',
}

export const getColor = (score) => {
	const settings = getSettings()
	const thresholdBad = settings.thresholdBad || 60
	if ( thresholdBad <= score ) {
		return 'red'
	}

	const thresholdGood = settings.thresholdGood || 30
	if ( thresholdGood >= score ) {
		return 'green'
	}

	return 'orange'
}
