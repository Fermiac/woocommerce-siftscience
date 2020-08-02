<template>
    <div>
        <div v-if="error" :style="iconStyle">
            <img :src="errorImage" :title="error.toString()" width="20px" height="20px" />
        </div>    
        
        <div v-if="isLoading" :style="iconStyle">
            <img :src="spinnerImage" title="Working..." width="20px" height="20px" />
        </div>    
        
        <div v-if="noData" :style="iconStyle" @click="backfill($event)">
            <img :src="uploadImage" title="Send this order to Sift" width="20px" height="20px" />
        </div>    
        
        <div v-if="hasData">
            <div title="User's Sift score" :style="scoreStyle" @click="openSiftSci($event)">
                <div :style="{ backgroundColor: scoreColor }">{{ score }}</div>
            </div>

            <div :title="goodTitle" :style="iconStyle" @click="clickGood($event)">
                <img :src="goodImage" title="good" width="20px" height="20px" />
            </div>    
        
            <div :title="goodTitle" :style="iconStyle" @click="clickBad($event)">
                <img :src="badImage" title="bad" width="20px" height="20px" />
            </div>    
        </div>
    </div>
</template>

<script>
import {getSettings, getLabel, setLabel, extractScore, extractLabel, backfill} from './api';
import {iconStyle, scoreStyle, getColor} from './styles';

export default {
    name: 'OrderControl',
    props: { id: String },
    async created() {
        try {
            await this.refresh()
        } catch (error) {
            this.error = error
            this.state = null
        }
    },
    data () {
        return {
            iconStyle, 
            scoreStyle: Object.assign({}, scoreStyle),
            state: 'loading',
            error: null,
            isBackfilled: false,
            score: 0,
            label: null,
            user_id: null,
        }
    },
    computed: {
        isLoading () { return this.state === 'loading' },
        errorImage() { return this.getImage('error.png') },
        spinnerImage() { return this.getImage('spinner.gif') },
        uploadImage() { return this.getImage('upload.png') },
        hasData () { return this.state === 'data' },
        noData () { return this.state === 'nodata' },
        goodTitle () { return this.getTitle('good') },
        badTitle () { return this.getTitle('bad') },
        goodImage () { return this.getLabelImage('good') },
        badImage () { return this.getLabelImage('bad') },
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
            return getSettings().imgPath + file
        },
        async setLabel(v, e) {
            try {
                e.preventDefault()
                e.stopPropagation()
                this.error = null
                this.state = 'loading'
                await setLabel(this.id, v === this.label ? null : v)
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
                await backfill(this.id)
                await this.refresh()        
            } catch (error) {
                this.error = error
                this.state = null
            }
        },
        async refresh() {
            this.error = null
            this.state = 'loading'
            const data = await getLabel(this.id)
            this.userId = data.sift.user_id
            this.score = extractScore(data.sift)
            this.label = extractLabel(data.sift)
            this.scoreStyle.backgroundColor = getColor(this.score)
            this.isBackfilled = data.is_backfilled
            const hasData = this.isBackfilled && this.score
            this.state = hasData ? 'data' : 'nodata'
        },
    } 
}
</script>
