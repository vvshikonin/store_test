<template>
    <td class="td" :class="classes()" :style="{'text-align': align}">
        <!-- <span> -->
            <slot></slot>
        <!-- </span> -->
    </td>
</template>
<style>
    .td.bold{
        font-weight: 700;
    }

    .td.currency span::after{
        content: '₽';
        padding-left: 3px;
    }
</style>
<script>
export default{
    props: {
        fw: String,
        align: String,
        currency: Boolean
    },
    methods:{
        classes: function(){
            let classes = {};

            if(this.fw == 'bold'){
                classes.bold = true;
            }

            if(this.currency){
                classes.currency = true;
            }

            return classes;
        }
    }

}
</script>