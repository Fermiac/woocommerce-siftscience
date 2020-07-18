const getApi = () => window._siftsci_app_data.api

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

	const text = await res.text()
	
    try {
        return JSON.parse(text)
    } catch (error) {
        error.text = text
        throw error
    }
}

const getLabelValue = (value) => {
	switch (value) {
		case 'bad':
			return 'set_bad'
		case 'good':
			return 'set_good'
		default:
			return 'unset'
	}
}

export const getSettings = () => window._siftsci_app_data

export const getLabel = (id) => api('score', id)
export const setLabel = (id, value) => api(getLabelValue(value), id)

export const backfill = (id) => api('backfill', id)
export const orderStats = () => api('order_stats', null)
export const clearAll = () => api('clear_all', null)

export const extractScore = (sift) => {
	if (sift.scores && sift.scores.payment_abuse) {
		return Math.round(sift.scores.payment_abuse.score * 100)
	}
	return null
}

export const extractLabel = (sift) => {
	if (sift.latest_labels && sift.latest_labels.payment_abuse) {
		return sift.latest_labels.payment_abuse.is_bad ? 'bad' : 'good'
	}
	return 'none'
}
