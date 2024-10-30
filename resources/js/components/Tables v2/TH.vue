<template>
    <td class="th" :class="classes" @click="field ? sort(field) : null" :style="styles">
        <div class="p-0">
            <span>
                <font-awesome-icon v-if="isSortable" :icon="sortIcon()"  size="sm" class=" pe-1" style="position: absolute; margin-left: -12px; margin-top: 3px;"/>
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
        field: String,
        title: String,
        width: String,
        align: String
    },
    inject:['params', 'sort'],
    computed:{
        styles(){
            return {
                'min-width': this.width,
                'text-align': this.align,
            }
        },
        classes(){
            return {
                sortable: this.isSortable,
                active: this.isActive
            }
        },
        isSortable(){
            return !!this.field;
        },
        isActive(){
            const currentSortField = this.params.sort_field;
            return this.field == currentSortField;
        }
    },
    methods:{
        sortIcon(){
            const currentSortField = this.params.sort_field;
            const currentSortType = this.params.sort_type;

            const ascIcon = 'fa-solid fa-sort-asc';
            const descIcon = 'fa-solid fa-sort-desc';
            const unsortedIcon = 'fa-solid fa-sort'

            if(currentSortField == this.field){
                return currentSortType == 'asc' ? ascIcon : descIcon;
            }
            return unsortedIcon;
        },
    }
}
</script>
