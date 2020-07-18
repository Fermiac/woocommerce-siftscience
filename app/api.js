const getApi = () => window._siftsci_app_data.api
export const getSettings = () => window._siftsci_app_data

const api = async (action, id) => {
	const idString = id ? '&id=' + id : '';
	const url = getApi() + '?action=wc_siftscience_action&wcss_action=' + action + idString;
	const res = await fetch(url, { credentials: 'same-origin' })
    if ( 200 > res.status || 300 < res.status ) {
        const msg = await res.text()
        const error = new Error('Server Error')
        error.text = msg
        throw error
    }

    const text = await res.text();
    try {
        return JSON.parse(text)
    } catch ( error ) {
        error.text = text
        throw error
    }
}

const labelActionMap = {
    bad: 'set_bad',
    good: 'set_good'
}

export const setLabel = ( id, value ) => {
	const action = labelActionMap[value] ? labelActionMap[value] : 'unset'
	return api(action, id)
};

export const backfill = (id) => {
	return api('backfill', id)
};

export const getLabel = (id) => {
	return api('score', id)
};

export const orderStats = () => {
	return api('order_stats', null)
};

export const clearAll = () => {
	return api('clear_all', null)
};

export const getUserData = (sift) => {
	if (!sift) {
		return null
	}

	const result = {}

	if (sift.scores && sift.scores.payment_abuse) {
		result.score = Math.round( sift.scores.payment_abuse.score * 100 )
	}

	result.label = 'none'
	if ( sift.latest_labels && sift.latest_labels.payment_abuse ) {
		result.label = sift.latest_labels.payment_abuse.is_bad ? 'bad' : 'good'
	}

	return result
}

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
	color: 'white',
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
