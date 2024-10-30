<template>
    <div v-if="meta.links" class="p-3 border-end per-page" style="width: 255px; font-size: 14px;">
        Показывать по:
        <a v-for="perPageValue in perPageVuluesArray" @click="changePerPage(perPageValue)" class="" :class="{active: checkPerPageIsActive(perPageValue)}">{{ perPageValue }}</a>
    </div>
    <div v-else  class="p-3 border-end  placeholder-glow text-center" style="width: 255px; font-size: 14px;">
        <span class="placeholder rounded" style="width: 200px; color: #6c757d;"></span>
    </div>

</template>
<style>
    .per-page a{
        text-decoration: none;
    }
    .per-page a:first-child{
        padding-left: 0.3rem;
    }
    .per-page a:not(:last-child)::after{
        content: '/';
        color: #6c757d;
        padding-left: 0.3rem;
        padding-right: 0.3rem;
    }
    .per-page a.active{
        color: #6c757d;
        pointer-events: none;
    }
    .per-page a:not(.active){
        cursor: pointer;
    }</style>
<script>
export default{
    props: {
        perPageVuluesArray: Array,
        meta: Object
    },
    methods:{
        changePerPage(per_page){
            this.$emit('per_page', per_page);
        },
        checkPerPageIsActive(buttonValue){
            return this.meta.per_page == buttonValue ? true : false;
        }
    },
}
</script>