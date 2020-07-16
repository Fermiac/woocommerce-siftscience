<template>
    <div>
        <order-icon v-if="error" :imgUrl="errorImageUrl" alt="error" />;
        <order-icon v-if="isWorking" :imgUrl="spinnerImageUrl" alt="error" />;
        <div v-if="hasData">
            <order-score id="id" score="score" userId="userId" />
            <order-label-button :imgPath="imgPath" :label="label" type="good" />
            <order-label-button :imgPath="imgPath" :label="label" type="bad" />
        </div>
    </div>
</template>

<script>
import OrderIcon from './OrderIcon.vue'
import OrderScore from './OrderScore.vue'
import OrderLabelButton from './OrderLabelButton.vue'

export default {
    name: 'OrderControl',
    components: { OrderIcon, OrderScore, OrderLabelButton },
    props: {
        imgPath: String,
        isBackfilled: Boolean,
        score: Number,
        label: String,
    },
    data () {
        return {
            error: false,
            isWorking: false,
        }
    },
    computed: {
        errorImageUrl() { return this.imgPath + 'error.png' },
        spinnerImageUrl() { return this.imgPath + 'spinner.png' },
        hasData() { return this.isBackfilled && this.score }
    },
}
</script>
