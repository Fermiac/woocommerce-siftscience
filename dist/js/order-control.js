const orders = [...document.getElementsByClassName( 'siftsci-order' )]

const appsOrders = orders && orders.map( order => new Vue({
    el: '#' + order.id,
    components: { OrderControl },
    template: '<order-control :id="id" />',
    data: { id: order.attributes['data-id'].value },
}))
