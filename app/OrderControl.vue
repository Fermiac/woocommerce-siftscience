<template>
    <div>
        <div v-if="error" :style="iconStyle">
            <img :src="errorImage" :alt="error" width="20px" height="20px" />
        </div>    
        
        <div v-if="isLoading" :style="iconStyle">
            <img :src="spinnerImage" alt="Working..." width="20px" height="20px" />
        </div>    
        
        <div v-if="hasData">
            <div title="User's SiftScience score" :style="scoreStyle" @click="openSiftSci">
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
import {getSettings, getLabel, setLabel, divStyle, iconStyle, scoreStyle} from './api';

export default {
    name: 'OrderControl',
    props: { id: String },
    async created() {
        const data = await getLabel(this.id)
        this.userId = data.sift.user_id
        this.score = Math.round(data.sift.scores.payment_abuse.score * 100)
        this.isBackfilled = data.is_backfilled
    },
    data () {
        return {
            divStyle, iconStyle, scoreStyle,
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
        errorImage() { return `${getSettings().imgPath}error.png` },
        spinnerImage() { return `${getSettings().imgPath}spinner.gif` },
        hasData () { return this.isBackfilled && this.score },
        goodTitle () { return 'good' === this.label ? 'Click to remove this label' : 'Click to set this label' },
        badTitle () { return 'bad' === this.label ? 'Click to remove this label' : 'Click to set this label' },
        goodImage () { return `${getSettings().imgPath}good` + ('good' === this.label ? '.png' : '-gray.png') },
        badImage () { return `${getSettings().imgPath}bad` + ('bad' === this.label ? '.png' : '-gray.png') },
    },
    methods: {
        async clickGood() {
            await setLabel('good' === this.label ? null : 'good')
        },
        async clickBad() {
            await setLabel('bad' === this.label ? null : 'bad')
        },
        openSiftSci () { window.open( 'https://siftscience.com/console/users/' + this.userId ) }
    } 
}
</script>
