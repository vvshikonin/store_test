<template>

    <MainTable :tableSettings="table" :meta="meta" @export="onExport()" @add_element="$router.push('/contractors/new')"
    @confirm_filter="onConfirmFilter()" @clear_filter="onClearFilter()" @current_page="onChangeCurrentPage($event)"
    :withHeadeSection="checkPermission('contractor_create')" @per_page="onChangePerPage($event)">
        <template v-slot:header></template>

        <template v-slot:filters>
            <FilterInput v-model="params.filters.name" label="Название поставщика" placeholder="Введите название"></FilterInput>
            <FilterInput v-model="params.filters.marginality" label="% наценки" placeholder="Введите процент" type="number"></FilterInput>
            <FilterInput v-model="params.filters.legalEntity" label="Юр.лицо" placeholder="Введите название юр.лица"></FilterInput>
            <FilterInput v-model="params.filters.minAmount" label="Мин. сумма заказа" placeholder="Введите сумму" type="number"></FilterInput>
            <FilterInput v-model="params.filters.pickupTime" label="Время забора" placeholder="Введите время"></FilterInput>
            <FilterInput v-model="params.filters.warehouse" label="Склад" placeholder="Введите адрес или часть адреса"></FilterInput>
            <FilterInput v-model="params.filters.workingCondition" label="Условия работы" placeholder="Введите условия или часть"></FilterInput>
            <FilterSelect v-model="params.filters.paymentDelay" label="Отсрочка платежа" :options="[{id: 1, name: 'Есть'}, {id: 0, name: 'Нет'}]"></FilterSelect>
            <FilterSelect v-model="params.filters.deliveryContract" label="Есть договор доставки" :options="[{id: 1, name: 'Есть'}, {id: 0, name: 'Нет'}]"></FilterSelect>
            <FilterSelect v-model="params.filters.mainContractor" label="Основной поставщик" :options="[{id: 1, name: 'Да'}, {id: 0, name: 'Нет'}]"></FilterSelect>
        </template>

        <template v-slot:thead>
            <TableHeader class="col-5" @click="onSort('name')" :sort="setSortProp('name')">Поставщик</TableHeader>
            <TableHeader class="col-2" @click="onSort('pickup_time')" :sort="setSortProp('pickup_time')">Время забора</TableHeader>
            <TableHeader class="col-2" @click="onSort('warehouse')" :sort="setSortProp('warehouse')">Склад</TableHeader>
            <TableHeader class="col-5" @click="onSort('working_conditions')" :sort="setSortProp('working_conditions')">Условия работы</TableHeader>
            <TableHeader class="col-2" @click="onSort('marginality')" :sort="setSortProp('marginality')">% наценки</TableHeader>
            <TableHeader class="col-2" @click="onSort('updated_at')" :sort="setSortProp('updated_at')">Дата изменения</TableHeader>
        </template>

        <template v-slot:tbody>
            <TableRow @click_row="toContractor(contractor.id)" v-for="contractor in contractors">
                <TableCell class="col-5"> {{ contractor.name }} </TableCell>
                <TableCell class="col-2"> {{ contractor.pickup_time }} </TableCell>
                <TableCell class="col-2"> {{ contractor.warehouse }} </TableCell>
                <TableCell class="col-5"> {{ contractor.working_conditions }} </TableCell>
                <TableCell class="col-2"> {{ contractor.marginality ? contractor.marginality + '%' : 'Не указан' }} </TableCell>
                <TableCell class="col-2"> {{ contractor.updated_at }} </TableCell>
            </TableRow>
        </template>
        <template v-slot:tfoot> <span class="pe-5" v-if="meta"> Всего поставщиков: {{ meta.total }} </span> </template>
    </MainTable>

</template>

<style>

</style>

<script>
import { mapGetters } from 'vuex';
import { contractorAPI } from '../api/contractor_api';

import MainTable from '../components/Tables/main_table.vue';
import TableHeader from '../components/Tables/th.vue';
import TableRow from '../components/Tables/tr.vue';
import TableCell from '../components/Tables/td.vue';
import FilterInput from '../components/inputs/filter_input.vue';
import FilterSelect from '../components/inputs/FilterSelect.vue';
import ModalWindow from '../components/modals/default_modal.vue';

export default {
    components:{ MainTable, TableHeader, TableRow, TableCell, FilterInput, FilterSelect, ModalWindow },
    data() {
        return {
            params: {
                page: 1,
                perPage: 25,
                filters: {
                    name: null,
                    marginality: null,
                    legalEntity: null,
                    minAmount: null,
                    pickupTime: null,
                    warehouse: null,
                    paymentDelay: null,
                    deliveryContract: null,
                    mainContractor: null,
                    workingCondition: null
                },
                sortField: 'name',
                sortType: 'asc',
            },
            table: {
                isLoading: true,
                tableTitle: 'Поставщики',
                withExport: true,
                withFilters: true,
                withAddButton: true,
                withFooter: true,
                isCover: false,
            },
        }
    },
    methods: {
        onChangeCurrentPage(page) {
            this.params.page = page;
            this.table.isCover = true;
            this.loadContractors();
        },
        onChangePerPage(perPage) {
            this.params.perPage = perPage;
            this.table.isCover = true;
            this.loadContractors();
        },
        onConfirmFilter() {
            this.table.isCover = true;
            this.loadContractors();
        },
        onClearFilter() {
            this.params.filters.name             = null;
            this.params.filters.marginality      = null;
            this.params.filters.legalEntity      = null;
            this.params.filters.minAmount        = null;
            this.params.filters.pickupTime       = null;
            this.params.filters.warehouse        = null;
            this.params.filters.paymentDelay     = null;
            this.params.filters.deliveryContract = null;
            this.params.filters.mainContractor   = null;
            this.params.filters.workingCondition = null;
            this.table.isCover = true;
            this.loadContractors();
        },
        onSort: function(field){
            if(field == this.params.sortField){
                this.params.sortType = this.params.sortType == 'desc' ? 'asc' : 'desc';
            }else{
                this.params.sortField = field;
                this.params.sortType = 'asc';
            }
            this.table.isCover = true;
            this.loadContractors();
        },
        setSortProp: function(field){
            let sortProp = {}
            if(field == this.params.sortField){
                sortProp.isActive = true;
                sortProp.type = this.params.sortType;
            }else{
                sortProp.isActive = false;
            }
            return sortProp;
        },
        loadContractors() {
            this.$store.dispatch('loadContractorsData', this.params).then(() => {
                this.table.isLoading = false;
                this.table.isCover = false;
            });
        },
        toContractor(id) {
            this.$router.push('/contractors/' + id + '/edit');
        },
        onExport() {
            this.showToast('Экспорт', 'Экспорт таблицы Поставщики начат', 'info');

            contractorAPI.export(this.params).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Выгрузка Поставщики.xlsx');
                document.body.appendChild(link);
                link.click();

                this.showToast('Экспорт завершен', 'Экспорт таблицы Поставщики завершен', 'success');
            });
        }
    },
    mounted() {
        this.loadContractors()
    },
    computed: {
        ...mapGetters({contractors: 'getContractors', meta: 'getContractorsMeta'})
    }
}
</script>
