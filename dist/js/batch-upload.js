const _sift_batch_upload = {
    template: `
<div>
    <button type="button" class="button-primary wc-sift-button" style="margin-right: 5px;" @click="clearAll">Clear Data</button>
    <button type="button" class="button-primary wc-sift-button" style="margin-right: 5px;" @click="backfill">Back-Fill</button>
    <button type="button" class="button-primary wc-sift-button" style="margin-right: 5px;" @click="refresh">Refresh</button>

    <p v-if="status === 'error'">{{ error.text || error.toString() }}</p>
    <p v-if="status === 'loading'">Loading...</p>
    <p v-if="status === 'backfill'">Backfilling order #{{ orderId }}</p>
    <p v-if="status === 'stats'">
        Orders: {{ notBackfilled.length + backfilled.length }} <br />
        Backfilled: {{ backfilled.length }} <br />
        Not Backfilled: {{ notBackfilled.length }}
    </p>
</div>`,
    name: 'BatchUpload',
    async created() {
        await this.refresh()
    },
    data() {
        return {
            error: null,
            status: 'loading',
            orderId: '',
            notBackfilled: [],
            backfilled: [],
        }
    },
    methods: {
        async clearAll() {
            try {
                this.status = 'loading'
                await window._sift_app_api('clear_all')
                await this.refresh()
            } catch (error) {
                this.status = 'error'
                this.error = error
            }
        },
        async backfill() {
            try {
                this.status = 'backfill'
                for (let i = 0; i < this.notBackfilled.length; i++) {
                    const id = this.notBackfilled[i]
                    this.orderId = id
                    await window._sift_app_api('backfill', id)
                }
                await this.refresh()
            } catch (error) {
                this.status = 'error'
                this.error = error
            }
        },
        async refresh() {
            try {
                this.status = 'loading'
                const data = await window._sift_app_api('order_stats')
                this.backfilled = data.backfilled
                this.notBackfilled = data.notBackfilled
                this.status = 'stats'
            } catch (error) {
                this.status = 'error'
                this.error = error
            }
        },
    },
}

Vue.createApp(_sift_batch_upload).mount('#batch-upload')
