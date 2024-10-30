<template>
     <div class="mt-3 p-2 bg-light shadow-lg rounded-top  border-start border-end border-top" style="border-left: 2px solid #dee2e6!important; border-right: 2px solid #dee2e6!important">
        <div class="d-flex flex-row align-items-center pb-1">
            <div class="d-flex flex-row align-items-center">            
                <h3 class="me-3">{{title}}</h3>
                <a @click.prevent="onShowFilter()" href="#" class="link-primary me-3 text-decoration-none">
                    <font-awesome-icon icon="fa-solid fa-filter" />
                    <span>Фильтры</span>
                </a>
                <!-- buttons slot -->
                <slot name="buttons"></slot>
            </div>
        </div>
        <Transition>
            <form  @submit.prevent="onConfirmFilter()">
                <div v-if="selection.filter.isShowFilter" class="border-top pt-3 d-flex flex-column filters">
                    <!-- filters input group slot -->
                    <slot name="filters-input-group"></slot>
                    <div class="d-flex flex-row pb-2">
                        <button type="submit"
                            class="btn btn-sm btn-primary bg-gradient me-2">Применить</button>
                        <button @click="onClearFilter()"  v-if="!selection.filter.isFilterEmpty"  type="button" class="btn btn-sm btn-danger bg-gradient ">
                            <font-awesome-icon icon="fa-solid fa-xmark" /> Очистить
                        </button>
                        <!-- <button  type="button" class="btn btn-outline-secondary bg-gradient border-0">
                            <font-awesome-icon icon="fa-solid fa-gear" size="md"/>
                        </button> -->
                    </div>
                </div>
            </form>
        </Transition>
    </div>
</template>
<script>
export default{
    inject:['title', 'selection', 'indexAction', 'defaultFilterAction'],
    methods:{
        onShowFilter: function () {
            this.selection.filter.isShowFilter = !this.selection.filter.isShowFilter;
        },
        onConfirmFilter: function(){
            this.selection.filter.isFilterEmpty = false;
            this.selection.pagination.page = 1;
            this.index();
        },
        onClearFilter: function () {  
            this.selection.filter.isFilterEmpty = true;
            this.$store.dispatch(this.defaultFilterAction);
            this.index();
        },
        index: function(){
            this.$root.$data.isLoading = true;
            return this.$store.dispatch(this.indexAction).then(()=>{
                this.$root.$data.isLoading = false;
            });
        },

    },

}
</script>