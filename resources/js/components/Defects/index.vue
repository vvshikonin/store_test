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
    components: { Header, Filters, TableBody, Table,  Pagination, TableSettingsModal },
    computed: {
        ...mapGetters({selection: 'getDefectsSelection', settings:'getDefectsSettings', indexData: 'getDefects'}),
    },
    data(){
        return{
            ls_name: 'defects'
        }
    },
    provide(){
        return {
            title: 'Браки',
            ls_name: this.ls_name,
            indexAction: 'indexDefects',
            defaultFilterAction: 'setDefectsFilterDefault',
            selection: computed(() => this.selection),
            settings: computed(() => this.settings),
            indexData: computed(() => this.indexData),
        }
    },
    mounted(){

        if(!JSON.parse(localStorage.getItem(this.ls_name+'-table'))){
            localStorage.setItem(this.ls_name+'-table', JSON.stringify(this.settings.tableDefault))
        }
        this.settings.table = this.settings.tableDefault;
    }
}
</script>
