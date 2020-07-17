<template>
    <div>
        <div v-if="error" class="siftsci_icon" @click="noop" >
            <img :src="errorImageUrl" alt="Error" width="20px" height="20px" />
        </div>    
        <div v-if="isWorking" class="siftsci_icon" @click="noop" >
            <img :src="spinnerImageUrl" alt="Working..." width="20px" height="20px" />
        </div>    
        <div v-if="hasData">
            <div :title="title" :id="id" className="siftsci_score" @click="openSiftSci">
                <div :style="{ backgroundColor: scoreColor }">{{ score }}</div>
            </div>

            <div :title="goodTitle" class="siftsci_icon" @click="clickGood">
                <img :src="goodImage" alt="good" width="20px" height="20px" />
            </div>    
        
            <div :title="goodTitle" class="siftsci_icon" @click="clickBad">
                <img :src="badImage" alt="bad" width="20px" height="20px" />
            </div>    
        </div>
    </div>
</template>

<script>
import api from './api';

export default {
    name: 'OrderControl',
    props: {
        imgPath: String,
        isBackfilled: Boolean,
        score: Number,
        label: String,
    },
    data () {
        return {
            divStyle,
            title: "The user's SiftScience score",
            error: false,
            isWorking: false,
        }
    },
    computed: {
        scoreColor() {
            const thresholdBad = settings.thresholdBad || 60;
            if ( thresholdBad <= this.score ) {
                return 'red';
            }

            const thresholdGood = settings.thresholdGood || 30;
            if ( thresholdGood >= this.score ) {
                return 'green';
            }

            return 'orange'
        },
        errorImageUrl() { return this.imgPath + 'error.png' },
        spinnerImageUrl() { return this.imgPath + 'spinner.png' },
        hasData() { return this.isBackfilled && this.score },
    },
    methods: {
        noop () {},
        async clickGood() {
            await setLabel('good' === this.label ? null : 'good')
        },
        async clickBad() {
            await setLabel('bad' === this.label ? null : 'bad')
        },
        goodTitle () {
            return 'good' === this.label ? 'Click to remove this label' : 'Click to set this label'
        },
        badTitle () {
            return 'bad' === this.label ? 'Click to remove this label' : 'Click to set this label'
        },
        goodImage() {
            return 'good' + ('good' === this.label ? '.png' : '-gray.png')
        },
        badImage() {           
            return 'bad' + ('bad' === this.label ? '.png' : '-gray.png')
        },
        openSiftSci () { window.open( 'https://siftscience.com/console/users/' + this.userId ) }
    } 
}
</script>

<style>
.siftsci_icon {
    width: '24px';
	display: 'block';
	float: 'none';
}

.siftsci_score {
 	color: 'white';
	text-align: 'center';
	border: '1px solid black';
    width: '24px';
	height: '20px';
	margin: '0px';   
	display: 'block';
	float: 'none';
}
</style>
