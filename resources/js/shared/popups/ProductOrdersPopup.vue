<template>
    <div class="orders-popup" :class="{active: checkActive(), empty:checkEmpty()}">
        <a @click.stop="null" v-for="order in content" target="_blank" :href="'https://babylissrus.retailcrm.ru/orders/' + order.crm_id + '/edit'" class="p-1" >
            {{ order.number }}<span> - {{order.amount}}шт.</span>
        </a>
    </div>
</template>
<style>
    .orders-popup{
        position: absolute;
        transition: all 0.2s ;
        text-overflow: ellipsis;
        line-height: 350%;
        width: 130px;
        overflow: hidden;
        white-space: nowrap;
        margin-top: -22px;
        border-color: #dee2e6;
        z-index: 10;
    }
    .orders-popup.empty::after{
        content: '—';
        padding-left: 5px;
    }
    .orders-popup a{
        text-decoration: none;
    }
    .orders-popup a:not(:last-child):after{
        content: ',';
    }
    .orders-popup.active span{
        display: none;
    }

    .orders-popup.active:hover a:not(:last-child):after{
        content: unset;
    }

    .orders-popup.active:hover{
        margin-top: -32px;
        position: absolute;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 1rem 1rem;
        background-color: white;
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
        box-shadow: 0 0.5rem 1rem rgb(0 0 0 / 15%) !important;
        min-height: 63px;
        line-height:initial!important;
        z-index: 100;
    }

    .orders-popup.active:hover span{
        display: initial;
    }

</style>
<script>
    export default{
        props: {
            content: {
                type: Array,
                default: [],
            },
        },
        methods:{
            checkActive: function(){
                if(this.content.length > 1){
                    return true;
                }
                return false;
            },
            checkEmpty: function(){
                if(this.content.length == 0){
                    return true;
                }
                return false;
            }
        }
    }
</script>
