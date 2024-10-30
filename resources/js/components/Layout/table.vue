<template>
    <button @click="onShowTableSettings()" type="button" class="btn btn-sm bg-gradient border-0 position-absolute translate-middle end-0 rounded-circle me-3 text-light"  style="background-color: #3985f3;">
        <font-awesome-icon icon="fa-solid fa-gear"/>
    </button>
    <table v-if="indexData.length" class="table table-hover bg-white mb-0 border-end border-start border-top" style="border-left: 2px solid #dee2e6!important; border-right: 2px solid #dee2e6!important;" >
        <thead class="table-primary text-nowrap">
            <tr>
                <template v-for="cell in settings.table" >
                    <th v-if="cell.visability"  @click="onSort(cell.field)"  style="cursor: pointer;">
                        <font-awesome-icon v-if="cell.text"  v-bind:icon="sortIcon(cell.field)" size="sm" color="#0d6efd" class="me-1"/>
                        {{ cell.text }}
                    </th>
                </template>
            </tr>
        </thead>
        <!-- tbody slot -->
        <slot></slot>
    </table>
    <div v-else>
        <div class="border bg-white p-5 d-flex justify-content-center text-muted"  :class="{'text-muted' : !this.$root.$data.isLoading, '' : this.$root.$data.isLoading}" style="border-left: 2px solid #dee2e6!important; border-right: 2px solid #dee2e6!important; min-height: 156px">
            <h1><font-awesome-icon icon="fa-solid fa-xmark" v-if="!this.$root.$data.isLoading" size="lg"/></h1>
        </div>
    </div>
</template>
<script>
export default{
    inject:['selection', 'settings', 'indexData', 'indexAction'],
    methods:{
        onSort: function(field){
            if(field){
                if(this.selection.sort.field == field){
                    this.selection.sort.type = this.selection.sort.type == 'asc' ? 'desc' : 'asc';
                }else{
                    this.selection.sort.field = field;
                }
                this.index();
            }

        },
        onShowTableSettings: function(){
            this.settings.isShowTableSettings = !this.settings.isShowTableSettings;
        },
        index: function(){
            this.$root.$data.isLoading = true;
            return this.$store.dispatch(this.indexAction).then(()=>{
                this.$root.$data.isLoading = false;
            });
        },
        sortIcon: function(field){
            if(field){
                return this.selection.sort.field != field ? 'fa-sort' : (this.selection.sort.type == 'asc' ? 'fa-sort-up' : 'fa-sort-down');
            }
            
        }
    },
    mounted(){
        this.index();
    }
    
}
</script>