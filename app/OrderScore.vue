<template>
    <div :title="title" :id="id" className="siftsci_score" :style="divStyle" @click="openSiftSci">
        <div :style="style">{{ score }}</div>
    </div>
</template>

<script>
import { settings } from './lib/api'

const divStyle = {
	width: '24px',
	display: 'block',
	float: 'none',
};

const scoreStyle = {
	color: 'white',
	textAlign: 'center',
	border: '1px solid black',
	width: '20px',
	height: '20px',
	margin: '0px',
};

const getColor = ( score ) => {
	const thresholdBad = settings.thresholdBad || 60;
	if ( thresholdBad <= score ) {
		return 'red';
	}

	const thresholdGood = settings.thresholdGood || 30;
	if ( thresholdGood >= score ) {
		return 'green';
	}

	return 'orange';
};

export default {
    name: 'OrderScore',
    props: {
        id: String,
        score: Number,
        userId: String,
    },
    data () {
        return { 
            divStyle,
            title: "The user's SiftScience score",
        }
    },
    computed: {
        style () {
            return Object.assign({}, scoreStyle, {
                backgroundColor: getColor(this.score),
            })
        }
    },
    methods: {
        openSiftSci () { window.open( 'https://siftscience.com/console/users/' + this.userId ) }
    } 
}
</script>
