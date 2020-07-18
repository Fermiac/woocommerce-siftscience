<template>
    <div>
        <button type="button" class="button-primary wc-sift-button" style="margin-right: 5px;" @click="clearAll">Clear Data</button>
        <button type="button" class="button-primary wc-sift-button" style="margin-right: 5px;" @click="backfill">Back-Fill</button>
        <button type="button" class="button-primary wc-sift-button" style="margin-right: 5px;" @click="refresh">Refresh</button>

        <p v-if="isError">{{ errorMessage }}</p>
        <p v-if="isLoading">Loading...</p>
        <p v-if="isBackfill">Backfilling order #{{ orderId }}</p>
		<p v-if="isStats">
			Orders: {{ totalOrders }} <br />
			Backfilled: {{ numBackfilled }} <br />
			Not Backfilled: {{ numNotBackfilled }}
		</p>
    </div>
</template>

<script>
import {backfill, orderStats, clearAll} from './api';

export default {
    name: 'BatchUpload',
    async created() { await this.refresh() },
    data () { return { 
        error: null,
        status: 'loading',
        orderId: '',
        notBackfilled: [],
        backfilled: [],
    } },
    computed: {
        isError () { return this.status === 'error' },
        errorMessage () { return this.error.text || this.error.toString() },
        isLoading () { return this.status === 'loading' },
        isBackfill () { return this.status === 'backfill' },
        isStats () { return this.status === 'stats' },
        totalOrders () { return this.notBackfilled.length + this.backfilled.length },
        numBackfilled () { return this.backfilled.length },
        numNotBackfilled () { return this.notBackfilled.length },
    },
    methods: {
        async clearAll() { 
            try {
                this.status = 'loading'
                await clearAll()
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
                    await backfill(id)
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
                const data = await orderStats() 
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
</script>
