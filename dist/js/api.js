window._sift_app_api = async (action, id) => {
    const idString = id ? '&id=' + id : '';
    const url = window._siftsci_app_data.api + '?action=wc_siftscience_action&wcss_action=' + action + idString;
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
