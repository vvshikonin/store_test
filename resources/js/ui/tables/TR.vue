<template>
    <tr  @click.left=" toggleInnerTable()" :class="{'alert-row': isAlert, 'pointer': (withInnerTable || cursorPointer)}">
        <td v-if="withInnerTable" class="text-center" >
            <font-awesome-icon icon="fa-solid fa-chevron-right" class="text-primary" :class="{'fa-rotate-90': isShowInnerTable}" style="transition: all 0.2s;"/>
        </td>
        <slot name="default"></slot>
    </tr>
    <Transition v-if="withInnerTable">
        <tr v-show="isShowInnerTable" class="bg-light" :class="{ expanded: isShowInnerTable}">
            <td colspan="100%" >
                <table class="table table-borderless mb-0 table-hover inner-table" >
                    <thead class="align-middle border-top border-bottom" style="font-size: 12px; height: 41px; border: 0px!important;">
                        <slot name="sub-thead"></slot>
                    </thead>
                    <tbody class="align-middle" style="font-size: 13px;">
                        <slot name="sub-tbody"></slot>
                    </tbody>
                </table>
            </td>
        </tr>
    </Transition>
</template>
<style>
    .pointer{
        cursor: pointer;
    }
    .alert-row, .alert-row:hover td{
        position: relative;
        --bs-text-opacity: 1;
        color: rgba(var(--bs-danger-rgb), var(--bs-text-opacity)) !important;
    }
</style>
<script>
export default{
    props: {
        withInnerTable: Boolean,
        isAlert: Boolean,
        cursorPointer: Boolean,
    },
    emits:["click_row", "inner_collaps", "inner_expand"],
    data(){
        return{
            isShowInnerTable: false
        }
    },
    methods:{
        toggleInnerTable: function(){

            if(window.getSelection().toString() == ""){
                this.isShowInnerTable = !this.isShowInnerTable;
                this.$emit('click_row');
                if(this.isShowInnerTable)
                {
                    this.$emit('inner_expand');
                } else {
                    this.$emit('inner_collaps');
                }
            }

        },
        moinRowClasses: function(){
            let classes = this.mainRowClasses;

            if(this.isShowInnerTable){
                classes.expanded = true;
            }else{
                classes.expanded = false;
            }

            if(this.withInnerTable){
                classes.pointer = true;
            }

            return classes;
        }
    },

}
</script>
