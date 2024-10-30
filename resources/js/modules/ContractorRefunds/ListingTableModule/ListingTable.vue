<template>
    <Table class="appl-test">
        <template v-slot:filters>
            <FilterInput v-model="params.invoice_number_filter" type="text" label="Счёт" placeholder="Номер счёта" />
            <FilterInput v-model="params.product_filter" type="text" label="Товар" placeholder="Артикул или название" />
            <FilterMultipleSelect v-model="params.contractor_filter" placeholder="Выбрать поставщика" label="Поставщик" :options="contractors" />
            <FilterSelect v-model="params.contractors_type_filter" label="Тип поставщика" :options="contractorTypes" />
            <filter_select_multiple v-model="params.is_complete_filter" :options="statusOption" type="text"
                label="Статус возврата" placeholder="Выберите" />
            <FilterInputBetween v-model="params.refund_sum_filter" type="number" step="0.01" label="Сумма возврата" />
            <FilterInput v-model="params.comment_filter" type="text" label="Комментарий" />
            <FilterInputBetween v-model="params.delivery_date_filter" type="date" label="Дата доставки" />
            <FilterInput v-model="params.delivery_address_filter" type="text" label="Адрес доставки" />
            <filter_select_multiple v-model="params.delivery_status_filter" :options="deliveryOption" type="text"
                label="Статус доставки" placeholder="Выберите" />
        </template>
        <template v-slot:thead>
            <TH align="center" field="contractor_refunds.id">№</TH>
            <TH class="fw-bold" field="invoices.number">Счёт</TH>
            <TH field="contractors.name">Поставщик</TH>
            <TH field="contractor_refunds.is_complete">Статус возврата</TH>
            <TH align="center" class="fw-bold" field="refund_sum">Сумма возврата</TH>
            <TH field="contractor_refunds.comment">Комментарий</TH>
            <TH field="contractor_refunds.delivery_date">Дата доставки</TH>
            <TH field="contractor_refunds.delivery_address">Адрес доставки</TH>
            <TH field="contractor_refunds.delivery_status">Статус доставки</TH>
            <TH field="contractor_refunds.created_at">Дата создания</TH>
        </template>
        <template v-slot:tbody>
            <TR v-for="refund in refunds" @click_row="$router.push('/contractor_refunds/' + refund.id + '/edit')">
                <TD align="center">{{ refund.id }}</TD>
                <TD class="fw-bold">
                    <a :href="'#/invoices/' + refund.invoice.id + '/edit'">
                        {{ refund.invoice.number }}
                    </a>
                </TD>
                <TD>{{ refund.invoice.contractor.name }}</TD>
                <TD>{{ refundStatus(refund.is_complete) }}</TD>
                <TD align="center" class="fw-bold bg-light border-end border-start border-1">{{
                    refund.refund_sum.priceFormat(true) }}</TD>
                <TD>{{ refund.comment || '—' }}</TD>
                <TD>{{ formatDate(refund.delivery_date, 'DD.MM.YYYY') }}</TD>
                <TD>{{ refund.delivery_address || '—' }}</TD>
                <TD>{{ deliveryStatus(refund.delivery_status) || '—' }}</TD>
                <TD>{{ formatDate(refund.created_at, 'DD.MM.YYYY HH:mm:ss') }}</TD>
            </TR>
            <ExportModal v-if="showExport" @refundsExport="refundsExport()" @refundProductsExport="refundProductsExport()" @close="showExport = false"
                title="Экспорт возвратов поставщикам" width="500px" />
        </template>
    </Table>
</template>

<script>
import IndexTableMixin from '../../../utils/indexTableMixin';
import { contractorRefundAPI } from '../../../api/contractor_refund';

import Table from '../../../ui/tables/Table.vue';
import FilterInput from '../../../ui/inputs/FilterInput.vue';
import FilterInputBetween from '../../../ui/inputs/FilterInputBetween.vue';
import TH from '../../../ui/tables/TH.vue';
import TR from '../../../ui/tables/TR.vue';
import TD from '../../../ui/tables/TD.vue';
import ExportModal from './components/ExportModal.vue';
import filter_select_multiple from '../../../components/inputs/filter_select_multiple.vue';
import { mapGetters } from 'vuex';

export default {
    components: { Table, FilterInput, FilterInputBetween, TR, TD, ExportModal, TH, filter_select_multiple },
    mixins: [IndexTableMixin],
    data() {
        return {
            showExport: false,
            refunds: [],
            statusOption: [
                { id: 0, name: 'Не завершён' },
                { id: 1, name: 'Завершён' }
            ],
            deliveryOption: [
                { id: 'complete', name: 'Доставлено' },
                { id: 'at_courier', name: 'У курьера' },
                { id: 'in_tc', name: 'В ТК' }
            ],
            contractorTypes: [
                { id: 1, name: 'Только основные' },
                { id: 0, name: 'Только дополнительные' }
            ],
        }
    },
    computed:{
        ...mapGetters({ contractors: 'getContractors' }),
    },
    mounted(){
        this.$store.dispatch('loadContractorsData');
    },
    methods: {
        initSettings() {
            this.settings.tableTitle = 'Возвраты поставщику';
            this.settings.localStorageKey = 'contractor_refunds';
            this.settings.withCreateButton = false;
            this.settings.withHeader = false;
            this.settings.withExport = true;
            this.settings.isLoading = true;
            this.settings.saveParams = true;
            this.settings.withBottomBox = false;
            this.settings.withFilterTemplates = true;
            this.settings.indexAPI = params => contractorRefundAPI.index(params);

            this.onInitData = res => {
                this.refunds = res.data.data
            }
            this.onExport = () => this.showExport = true;
            this.onInitParamsDefault = defaultParams => {
                defaultParams.sort_field = this.params.sort_field || 'contractor_refunds.created_at';
                defaultParams.sort_type = this.params.sort_type || 'desc';
            }
        },
        refundStatus(status) {
            return status ? 'Завершён' : 'Не завершён';
        },
        deliveryStatus(status) {
            switch (status) {
                case 'complete':
                    return 'Доставлено';
                case 'at_courier':
                    return 'У курьера';
                case 'in_tc':
                    return 'В ТК';
            }
        },
        downloadFile(response, fileName) {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName + '.xlsx');
            document.body.appendChild(link);
            link.click();
        },
        async refundsExport() {
            const res = await contractorRefundAPI.export(this.params);
            this.downloadFile(res, 'Выгрузка возвратов поставщику');
            this.showExport = false;
        },
        async refundProductsExport() {
            const res = await contractorRefundAPI.exportProducts(this.params);
            this.downloadFile(res, 'Выгрузка возвратных товаров поставщику');
            this.showExport = false;
        }
    }
}
</script>
