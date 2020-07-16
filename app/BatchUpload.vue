<template>
    <div>
        <button type="button" className="button-primary" @click="clearAll">Clear Data</button>
        <button type="button" className="button-primary" @click="backfill">Back-Fill</button>
        <button type="button" className="button-primary" @click="refresh">Refresh</button>

        <p v-if="status == 'error'">{{ error.toString() }}:{{ error.text }}</p>;
        <p v-if="status == 'loading'">Loading...</p>
        <p v-if="status == 'backfill'">Backfilling order #{{ orderId }}</p>
		<p v-if="status == 'stats'">
			Orders: {{ totalOrders }} <br />
			Backfilled: {{ numBackfilled }} <br />
			Not Backfilled: {{ numNotBackfilled }}
		</p>
    </div>
</template>

<script>
import api from './lib/api';

export default {
    name: 'BatchUpload',
    props: {
        notBackfilled: Array,
        backfilled: Array,
    },
    data () { return { 
        error: null,
        status: 'loading',
        orderId: '',
    } },
    computed: {
        totalOrders () { return this.notBackfilled.length + this.backfilled.length },
        numBackfilled () { return this.backfilled.length },
        numNotBackfilled () { return this.notBackfilled.length },
    },
    methods: {
        handleError(error) {
            this.status = 'error'
            this.error = error
        },
        clearAll() { alert('clearAll clicked') },
        async backfill() { 
            try {
                this.status = 'backfill'
                for (let i = 0; i < this.notBackfilled.length; i++) {
                    const id = this.notBackfilled[i]
                    this.orderId = id
                    const data = await api.backfill(id)
                    Object.assign(this, data)
                }
            } catch (error) {
                this.handleError(error)
            }
         },
        refresh() {
            api.orderStats() 
                .then((data) => {
                    this.status = 'stats'
                    Object.assign(this, data)
                })
                .catch(this.handleError)
        },
    },
}
</script>
