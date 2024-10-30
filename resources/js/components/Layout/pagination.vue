<template>
    <div  class="bg-white p-2 pt-3 pb-3 border-start border-end border-bottom rounded-bottom d-flex align-items-center justify-content-between" style="border-left: 2px solid #dee2e6!important; border-right: 2px solid #dee2e6!important; min-height: 71px;">
        <nav aria-label="breadcrumb" class="d-flex align-items-baseline" v-if="selection.pagination.count != 1 ">
            <ol class="breadcrumb mb-0" >
                <li><span class="pe-2">Показывать по:</span></li>
                <li @click="changeLimit(25)" class="breadcrumb-item" :class="{'active pe-none': activeLimit(25), 'text-primary pe-pointer': !activeLimit(25)}" >25</li>
                <li @click="changeLimit(50)" class="breadcrumb-item" :class="{'active pe-none': activeLimit(50), 'text-primary pe-pointer': !activeLimit(50)}">50</li>
                <li @click="changeLimit(100)" class="breadcrumb-item" :class="{'active pe-none': activeLimit(100), 'text-primary pe-pointer': !activeLimit(100)}">100</li>
            </ol>
        </nav>
        <nav aria-label="..." v-if="selection.pagination.count != 1" >
            <ul class="pagination mb-0">
                <li @click.prevent="previous()" class="page-item" :class="{'disabled pe-none': previousButtonDisabled()}">
                    <a class="page-link" href="#">Назад</a>
                </li>

                <template v-if="selection.pagination.count > 7">
                    <template v-if="selection.pagination.page <= 4">
                        <li v-for="index in 5" @click.prevent="changePage(index)" :class="{'active pe-none': activePage(index)}" class="page-item">
                            <a class="page-link" href="#">{{index}}</a>
                        </li>
                        <li class="page-item disabled">
                            <a class="page-link">...</a>
                        </li>
                        <li @click.prevent="changePage(selection.pagination.count)" :class="{'active pe-none': activePage(selection.pagination.count)}" class="page-item">
                            <a class="page-link" href="#">{{selection.pagination.count}}</a>
                        </li>
                    </template>

                    <template v-else-if="selection.pagination.page > selection.pagination.count - 4">
                        <li  @click.prevent="changePage(1)" :class="{'active pe-none': activePage(1)}" class="page-item">
                            <a class="page-link"  href="#">{{1}}</a>
                        </li>
                        <li class="page-item disabled">
                            <a class="page-link">...</a>
                        </li>
                        <li v-for="index in 5" @click.prevent="changePage(selection.pagination.count - 5 + index)" :class="{'active pe-none': activePage(selection.pagination.count - 5 + index)}" class="page-item">
                            <a class="page-link" href="#">{{selection.pagination.count - 5 + index}}</a>
                        </li>
                    </template>

                    <template v-else>
                        <li  @click.prevent="changePage(1)" :class="{'active pe-none': activePage(1)}" class="page-item">
                            <a class="page-link" href="#">{{1}}</a>
                        </li>
                        <li class="page-item disabled">
                            <a class="page-link">...</a>
                        </li>
                        <li  @click.prevent="changePage(selection.pagination.page-1)" :class="{'active pe-none': activePage(selection.pagination.page-1)}" class="page-item">
                            <a class="page-link" href="#">{{selection.pagination.page-1}}</a>
                        </li>
                        <li  @click.prevent="changePage(selection.pagination.page)" :class="{'active pe-none': activePage(selection.pagination.page)}" class="page-item">
                            <a class="page-link" href="#">{{selection.pagination.page}}</a>
                        </li>
                        <li  @click.prevent="changePage(selection.pagination.page+1)" :class="{'active pe-none': activePage(selection.pagination.page+1)}" class="page-item">
                            <a class="page-link" href="#">{{selection.pagination.page+1}}</a>
                        </li>
                        <li class="page-item disabled">
                            <a class="page-link">...</a>
                        </li>
                        <li  @click.prevent="changePage(selection.pagination.count)" :class="{'active pe-none': activePage(selection.pagination.count)}" class="page-item">
                            <a class="page-link" href="#">{{selection.pagination.count}}</a>
                        </li>
                    </template>
                </template>

                <template v-else>
                    <li v-for="index in selection.pagination.count" @click.prevent="changePage(index)" :class="{'active pe-none': activePage(index)}" class="page-item">
                        <a class="page-link" href="#">{{index}}</a>
                    </li>
                </template>
                <li @click.prevent="next()" class="page-item" :class="{'disabled pe-none': nextButtonDisabled()}">
                    <a class="page-link" href="#">Вперёд</a>
                </li>
            </ul>
        </nav>
    </div>
</template>
<script>
export default{
    inject:['selection', 'indexAction'],
    methods:{
        index: function(){
            this.$root.$data.isLoading = true;
            this.$store.dispatch(this.indexAction).then(()=>{
                this.$root.$data.isLoading = false;
            })
        },
        changeLimit: function(limit){
            this.selection.pagination.limit = limit;
            this.selection.pagination.page = 1;
            this.index();
        },
        changePage: function(page){
            this.selection.pagination.page = page;
            this.index();
        },
        previous: function(){
            this.selection.pagination.page--;
            this.index();
        },
        next: function(){
            this.selection.pagination.page++;
            this.index();
        },
        activeLimit: function(limit){
            if(this.selection.pagination.limit == limit){
                return true;
            }
            return false;
        },
        activePage: function(page){
            if(this.selection.pagination.page == page){
                return true;
            }
            return false;
        },
        previousButtonDisabled: function(){
            if(this.selection.pagination.page == 1){
                return true;
            }
            return false
        },
        nextButtonDisabled: function(){
            if(this.selection.pagination.page == this.selection.pagination.count){
                return true;
            }
            return false
        }
    },
}
</script>