<template>
    <!-- to do доделать, когда будет ресурс возврата -->
    <modal title="Возвраты поставщику">
        <SpinnerBlock class="p-5" v-if="isLoading" />
        <template v-else>
            <template v-if="this.$store.state.invoiceModule.refunds.length">
                <div class="p-3"></div>
                <Table class="mb-5 border-start-0 border-end-0 border-bottom-0">
                    <template v-slot:thead>
                        <TH align="center" class="fw-bold">№</TH>
                        <TH>Дата создания</TH>
                        <TH>Статус</TH>
                        <TH>Комментарий</TH>
                        <TH align="center">Сумма</TH>
                        <TH>Пользователь</TH>
                        <TH></TH>
                    </template>
                    <template v-slot:tbody>
                        <TR v-for="refund in filteredRefunds">
                            <TD align="center" class="fw-bold">{{ refund.id }}</TD>
                            <TD>{{ formatDate(refund.created_at, 'DD.MM.YYYY HH:mm:ss') }}</TD>
                            <TD>{{ refundStatus(refund.is_complete) }}</TD>
                            <TD>
                                <span>{{ refund.comment || '—' }}</span>
                            </TD>
                            <TD align="center" class="bg-light border-start border-end border-1">
                                {{refund.refund_sum.priceFormat(true) }}
                            </TD>
                            <TD>
                                <span class="ms-3">{{ refund.creator.name }}</span>
                            </TD>
                            <TD width="50">
                                <a :href="'#/contractor_refunds/' + refund.id + '/edit'"
                                    class="btn btn-outline-primary border-0" title="Редактировать">
                                    <font-awesome-icon icon="fa-regular fa-pen-to-square" />
                                </a>
                            </TD>
                        </TR>
                    </template>
                </Table>
            </template>

            <div v-else class="d-flex flex-column align-items-center w-100 p-5 m-5 text-muted">
                <span>Нет возвратов поставщику по этому счёту,</span>
                <span>создайте их с помощью кнопки "Создать возврат" на странице счёта</span>
            </div>
        </template>

    </modal>
</template>

<script>
import modal from '../../../../ui/modals/modal.vue';
import Table from '../../../../ui/tables/Table.vue';
import TH from '../../../../ui/tables/TH.vue';
import TR from '../../../../ui/tables/TR.vue';
import TD from '../../../../ui/tables/TD.vue';
import SpinnerBlock from '../../../../ui/spinners/SpinnerBlock.vue'

export default {
    components: { modal, Table, TH, TR, TD, SpinnerBlock },
    data() {
        return {
            isLoading: true,
        }
    },
    computed: {
        filteredRefunds() {
            return this.$store.state.invoiceModule.refunds
        }
    },
    async mounted() {
        await this.$store.dispatch('invoiceModule/loadRefunds');
        this.isLoading = false;
    },
    methods: {
        refundStatus(status) {
            return status ? 'Завершён' : 'Не завершён';
        }
    }
}
</script>