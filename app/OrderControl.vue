<template>
    <div>
        <div v-if="error" :style="iconStyle">
            <img :src="errorImage" :alt="error" width="20px" height="20px" />
        </div>    
        
        <div v-if="isLoading" :style="iconStyle">
            <img :src="spinnerImage" alt="Working..." width="20px" height="20px" />
        </div>    
        
        <div v-if="hasData">
            <div title="User's SiftScience score" :style="scoreStyle" @click="openSiftSci($event)">
                <div :style="{ backgroundColor: scoreColor }">{{ score }}</div>
            </div>

            <div :title="goodTitle" :style="iconStyle" @click="clickGood">
                <img :src="goodImage" alt="good" width="20px" height="20px" />
            </div>    
        
            <div :title="goodTitle" :style="iconStyle" @click="clickBad">
                <img :src="badImage" alt="bad" width="20px" height="20px" />
            </div>    
        </div>
    </div>
</template>

<script>
import {getSettings, getLabel, setLabel, extractScore, extractLabel} from './api';
import {iconStyle, scoreStyle, getColor} from './styles';

export default {
    name: 'OrderControl',
    props: { id: String },
    created() { this.refresh() },
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
        hasData () { return this.state === 'data' },
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
            e.preventDefault()
            e.stopPropagation()
            window.open('https://siftscience.com/console/users/' + this.userId) 
        },
        getLabelImage(v) {
            const ending = v === this.label ? '.png' : '-gray.png'
            return this.getImage(v + ending)
        },
        getImage(file) {
            return getSettings().imgPath + file
        },
        async setLabel(v, e) {
            e.preventDefault()
            e.stopPropagation()
            this.error = null
            this.state = 'loading'
            await setLabel(this.id, v === this.label ? null : v)
            await this.refresh()
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
            this.state = 'data'
        },
        async wrap(promise) {
            try {
                await promise
            } catch (error) {
                this.error = error
                this.state = null;
            }
        }
    } 
}
</script>