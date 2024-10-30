<template>
    <div>
        <Header>
            <template #filters-input-group>
                <Filters></Filters>
            </template>
        </Header>
        <Table>
            <TableBody></TableBody>
        </Table>
        <Pagination></Pagination>
        <TableSettingsModal></TableSettingsModal>
    </div>
</template> 

<script>
import Filters from './filters.vue';
import Table from '../Layout/table.vue';
import Header from '../Layout/header.vue'
import TableBody from './table_body.vue';
import Pagination from '../Layout/pagination.vue';
import TableSettingsModal from '../Layout/table_settings_modal.vue'
import { mapGetters } from 'vuex';
import { computed } from 'vue'
export default {
    components: { Header, Filters, Table, TableBody, Pagination, TableSettingsModal },
    computed: {
        ...mapGetters({selection: 'getProductRefundSelection', settings:'getProductRefundSettings', indexData: 'getProductRefund'}),
    },
    data(){
        return{
            ls_name: 'productRefund'
        }
    },
    provide(){
        return {
            title: 'Возврат товара',
            ls_name: this.ls_name,
            indexAction: 'indexProductRefund',
            defaultFilterAction: 'setFilterDefaultMoneyRefund',
            selection: computed(() => this.selection),
            settings: computed(() => this.settings),
            indexData: computed(() => this.indexData),
        }
    },
    mounted(){
        if(!JSON.parse(localStorage.getItem(this.ls_name+'-table'))){
            localStorage.setItem(this.ls_name+'-table', JSON.stringify(this.settings.tableDefault))
        }
        this.settings.table = JSON.parse(localStorage.getItem(this.ls_name+'-table'));
    }
}
</script>
