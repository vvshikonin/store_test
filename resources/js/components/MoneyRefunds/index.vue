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
        <div class="d-flex flex-rowend ps-2 bg-white border-2 border-start border-end  w-100" style="width: 400px;">
            <span >Сумма по возвратам: <b>{{moneyRefundsSum.toFixed(2)}}</b></span>
        </div>
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
        ...mapGetters({selection: 'getMoneyRefundSelection', settings:'getMoneyRefundSettings', indexData: 'getMoneyRefund', userId: 'getUserId',  moneyRefunds: 'getMoneyRefund'}),
        moneyRefundsSum(){ return this.moneyRefunds.reduce((sum, refund) => sum + refund.sum, 0)} 

    },
    data(){
        return{
            ls_name: 'moneyRefund'
        }
    },
    provide(){
        return {
            title: 'Возврат ДС',
            ls_name: this.ls_name,
            indexAction: 'indexMoneyRefund',
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
