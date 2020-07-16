<template>
    <div :title="title" :id="id" class="siftsci_icon" :style="divStyle" @click="onclick" >
        <img :src="imgUrl" :alt="alt" width="20px" height="20px" />
    </div>    
</template>

<script>
export default {
    name: 'OrderIcon',
    props: {
        id: String,
        title: String,
        imgURl: String,
        alt: String,
        onclick: Function,
    },
    data () {
        return {
            divStyle: {
                width: '24px',
                display: 'block',
                float: 'none',
            }
        }
    },
}
</script>
