const _sift_order = {
    template: `
    <div>
        <div v-if="error" :style="iconStyle">
            <img :src="getImage('error.png')" :title="error.toString()" width="20" height="20" />
        </div>    
        
        <div v-if="state === 'loading'" :style="iconStyle">
            <img :src="getImage('spinner.gif')" title="Working..." width="20" height="20" />
        </div>    
        
        <div v-if="state === 'nodata'" :style="iconStyle" @click="backfill($event)">
            <img :src="getImage('upload.png')" title="Send this order to Sift" width="20" height="20" />
        </div>    
        
        <div v-if="state === 'data'">
            <div title="User's Sift score" :style="scoreStyle" @click="openSiftSci($event)">
                <div>{{ score }}</div>
            </div>

            <div :title="getTitle('good')" :style="iconStyle" @click="clickGood($event)">
                <img :src="getLabelImage('good')" title="good" width="20" height="20" />
            </div>    
        
            <div :title="getTitle('bad')" :style="iconStyle" @click="clickBad($event)">
                <img :src="getLabelImage('bad')" title="bad" width="20" height="20" />
            </div>    
        </div>
    </div>
    `,
    name: 'OrderControl',
    mounted() {
      console.log(`state is ${this.state}`)
    },
    data () {
        return {
            id: '',
            iconStyle: {
                width: '24px',
                display: 'block',
                float: 'none',
            },
            scoreStyle: {
                color: 'black',
                textAlign: 'center',
                border: '1px solid black',
                width: '20px',
                height: '20px',
                margin: '0px',
                backgroundColor: 'white',
            },
            state: 'loading',
            error: null,
            isBackfilled: false,
            score: 0,
            label: null,
            user_id: null,
        }
    },
    methods: {
        clickGood(e) { this.setLabel('good', e) },
        clickBad(e) { this.setLabel('bad', e) },
        getTitle(v) { return v === this.label ? 'Click to remove this label' : 'Click to set this label'},
        openSiftSci (e) {
            try {
                e.preventDefault()
                e.stopPropagation()
                if (this.id) throw new Error('something something')
                window.open('https://sift.com/console/users/' + this.userId)
            } catch (error) {
                this.error = error
                this.state = null
            }
        },
        getLabelImage(v) {
            const ending = v === this.label ? '.png' : '-gray.png'
            return this.getImage(v + ending)
        },
        getImage(file) {
            return window._siftsci_app_data.imgPath + file
        },
        async setLabel(v, e) {
            try {
                e.preventDefault()
                e.stopPropagation()
                this.error = null
                this.state = 'loading'
                const label = this.getLabelValue(v === this.label ? null : v)
                await window._sift_app_api(label, this.id)
                await this.refresh()
            } catch (error) {
                this.error = error
                this.state = null
            }
        },
        async backfill(e) {
            try {
                e.preventDefault()
                e.stopPropagation()
                this.error = null
                this.state = 'loading'
                await window._sift_app_api('backfill', this.id)
                await this.refresh()
            } catch (error) {
                this.error = error
                this.state = null
            }
        },
        async refresh() {
            this.error = null
            this.state = 'loading'
            const data = await window._sift_app_api('score', this.id)
            this.userId = data.sift.user_id
            this.score = this.extractScore(data.sift)
            this.label = this.extractLabel(data.sift)
            this.scoreStyle.backgroundColor = this.getColor(this.score)
            this.isBackfilled = data.is_backfilled
            const hasData = this.isBackfilled && this.score
            this.state = hasData ? 'data' : 'nodata'
        },
        getColor(score) {
            const settings = window._siftsci_app_data
            const thresholdBad = settings.thresholdBad || 60
            if ( thresholdBad <= score ) {
                return 'red'
            }

            const thresholdGood = settings.thresholdGood || 30
            if ( thresholdGood >= score ) {
                return 'green'
            }

            return 'orange'
        },
        extractScore(sift) {
            if (sift.scores && sift.scores.payment_abuse) {
                return Math.round(sift.scores.payment_abuse.score * 100)
            }
            return null
        },
        extractLabel(sift) {
            if (sift.latest_labels && sift.latest_labels.payment_abuse) {
                return sift.latest_labels.payment_abuse.is_bad ? 'bad' : 'good'
            }
            return 'none'
        },
        getLabelValue(value) {
            switch (value) {
                case 'bad':
                    return 'set_bad'
                case 'good':
                    return 'set_good'
                default:
                    return 'unset'
            }
        },
    }
}

const _sift_apps = [...document.getElementsByClassName( 'siftsci-order' )].map( order => {
    const el = '#' + order.id
    console.log(`mounting: ${el}`)
    const app = Vue.createApp(_sift_order).mount(el)
    app.$data.id = order.attributes['data-id'].value
    app.refresh()
    return app
})
