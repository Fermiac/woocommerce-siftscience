<template>
    <order-icon :title="title" :imgUrl="imgUrl" :alt="type" @click="callback" />
</template>

<script>
import OrderIcon from './OrderIcon.vue'
import { setLabel } from './lib/api'

export default {
    name: 'OrderLabelButton',
    components: { OrderIcon },
    props: {
        type: String,
        label: String,
        imgPath: String,
    },
    data() {
        const isSet = this.type === this.label;
        const title = isSet ? 'Click to remove this label' : 'Click to set this label';
        const image = this.type + ( isSet ? '.png' : '-gray.png' );
        const callback = () => setLabel( isSet ? null : this.type );
        return { title, image, callback }
    },
    computed:{ 
        imgUrl () { return this.imgPath + this.image }
    },
}
</script>
