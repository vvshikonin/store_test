<template>
    <td class="th" :class="sortClasses()" :style="{'min-width': width + 'px', 'text-align': align}"> 
        <div class="p-0">
            <span>
                <font-awesome-icon v-if="sort" :icon="sortIcon()"  size="sm" class=" pe-1" style="position: absolute; margin-left: -12px; margin-top: 3px;"/>
                <slot></slot>
            </span>
            <span v-if="title" :title="title"  class="ps-1 hint" style="position: absolute;">
                <font-awesome-icon icon="fa-regular fa-circle-question" size="lg"/>
            </span>
        </div>
    </td>
</template>
<style>
    .th{
        color: #6c757d;
        padding-left: 15px;
    }
    .th.sortable{
        cursor:pointer;
    }
    .th.sortable.active{
        color: #0d6efd;
        border-bottom: 1px #0d6efd solid;
    }
    .inner-table thead .th.sortable.active{
        border-bottom: 0px;
    }
    .hint{
        color: #6c757d;
        cursor: pointer;
    }
    .hint:hover{
        color: #0d6efd;
    }

</style>
<script>
export default{
    props: {
        sort:{
            default: null,
            type: Object
        },
        title: String,
        width: String,
        align: String
    },
    methods:{
        sortIcon: function(){
            if(this.sort && this.sort.isActive){
                return this.sort.type == 'asc' ? 'fa-solid fa-sort-asc' : 'fa-solid fa-sort-desc';
            }
            return 'fa-solid fa-sort';
        },
        sortClasses: function(){
            let sortClasses = {};

            if(this.sort){
                sortClasses['sortable'] = true;

                if(this.sort.isActive){
                    sortClasses['active'] = true;
                }
            }
            return sortClasses;
        },


    }
}
</script>