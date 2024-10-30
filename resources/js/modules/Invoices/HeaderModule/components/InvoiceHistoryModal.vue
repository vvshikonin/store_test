<template>
    <Modal title="История по счёту">
        <SpinnerBlock class="p-5" v-if="isLoading" />

        <template v-else>
            <div class="p-2 d-flex align-items-center justify-content-end">
                <button v-for="(table, index) in fixedTables" :key="index" style="font-size: 12px;"
                    class="btn ms-1" :class="selectedTable === index ? 'btn-primary' : 'btn-outline-primary'"
                    @click.prevent="selectedTable = index">
                    {{ table.name }}
                </button>
            </div>
            <div style="max-height: 60vh; overflow-y: auto;">
                <Table v-if="selectedTableList.length && selectedTable" class="border-start-0 border-end-0 border-bottom-0">
                    <template v-slot:thead>
                        <TH align="center">Артикул</TH>
                        <TH>Название товара</TH>
                        <TH align="center">Количество</TH>
                        <TH align="center">Пользователь</TH>
                        <TH align="center">Дата</TH>
                    </template>
                    <template v-slot:tbody>
                        <TR v-for="entity in selectedTableList" :key="entity.id">
                            <TD class="fw-bold" align="center">
                                <a :href="getProductLink(entity)"> {{ entity.product_sku }} </a>
                            </TD>
                            <TD> {{ entity.product_name }} </TD>
                            <TD align="center">{{ getAmount(entity) }}</TD>
                            <TD align="center">{{ entity.user_name }}</TD>
                            <TD align="center">{{ formatDate(entity.refused_at || entity.created_at, 'DD.MM.YYYY HH:mm:ss') }}</TD>
                        </TR>
                    </template>
                </Table>
                <Table v-else-if="selectedTableList.length && selectedTable === 0" class="border-start-0 border-end-0 border-bottom-0">
                    <template v-slot:thead>
                        <TH align="center">Дата оплаты</TH>
                        <TH align="center">Статус</TH>
                        <TH align="center">Способ оплаты</TH>
                        <TH align="center">Пользователь</TH>
                        <TH align="center">Дата изменения статуса оплаты</TH>
                    </template>
                    <template v-slot:tbody>
                        <TR v-for="payment in payments">
                            <TD align="center">{{ formatDate(payment.payment_date, 'DD.MM.YYYY') }}</TD>
                            <TD align="center">{{ paymentStatuses.find(status => status.id === payment.status).name }}</TD>
                            <TD align="center">{{ payment.legal_entity.name + ' ' + payment.payment_method.name }}</TD>
                            <TD align="center">{{ payment.user.name }}</TD>
                            <TD align="center">{{ formatDate(payment.created_at, 'DD.MM.YYYY HH:mm:ss') }}</TD>
                        </TR>
                    </template>
                </Table>
                <div v-else class="p-5 text-center">
                    <span>Нет подходящих операций</span>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script>
import Modal from '../../../../ui/modals/modal.vue';
import Table from '../../../../ui/tables/Table.vue';
import SpinnerBlock from '../../../../ui/spinners/SpinnerBlock.vue';
import TH from '../../../../ui/tables/TH.vue';
import TR from '../../../../ui/tables/TR.vue';
import TD from '../../../../ui/tables/TD.vue';

export default {
    components: { Modal, Table, TH, TR, TD, SpinnerBlock },
    props: {
        transactions: {
            type: Array,
            required: true,
            default: () => [],
        },
        refused: {
            type: Array,
            required: true,
            default: () => [],
        },
        payments: {
            type: Array,
            required: true,
            default: () => [],
        },
    },
    data() {
        return {
            selectedTable: 0,
            fixedTables: [
                { name: 'Оплаты', value: 0 },
                { name: 'Отказы', value: 1 },
                { name: 'Оприходы', value: 2 },
            ],
            paymentStatuses: [
                { id: 0, name: 'Не платить' },
                { id: 1, name: 'Оплачен' },
                { id: 2, name: 'Требует оплаты' },
            ],
        };
    },
    methods: {
        closeModal() {
            this.$emit('close');
        },
        getProductLink(entity) {
            return '/#/products/' + entity.product_id + '/edit';
        },
        getAmount(entity) {
            if (entity.type)
                return entity.type === 'In' ? `+${entity.amount}` : `-${entity.amount}`;

            return entity.amount > 0 ? `+${entity.amount}` : entity.amount;
        },
    },
    computed: {
        selectedTableList() {
            switch (this.selectedTable)
            {
                case 0:
                    return this.payments;
                case 1:
                    return this.refused;
                case 2:
                    return this.transactions;
                default:
                    return this.payments;
            }
        },
    },
};
</script>