<template>
    <Modal :title="title" @close_modal="$emit('close')">
        <div style="max-height: 100%; overflow-y: auto;">
            <Table style="border: 0px!important; ">
                <template v-slot:thead>
                    <TH>Артикул </TH>
                    <TH>Название товара</TH>
                    <TH align="center">Количество</TH>
                    <TH>Пользователь</TH>
                    <TH>Время</TH>
                </template>
                <template v-slot:tbody>
                    <TR v-for="transaction in transactions">
                        <TD class="ps-3">
                            <a :href="getProductLink(transaction)"> {{ transaction.product_sku }}</a>
                        </TD>
                        <TD class="ps-3">{{ transaction.product_name }}</TD>
                        <TD align="center">
                            {{ (transaction.type == 'Out' ? '-' : "") + transaction.amount }}
                        </TD>
                        <TD>{{ transaction.user_name }}</TD>
                        <TD>{{ formatDate(transaction.created_at, 'DD.MM.YYYY HH:mm:ss') }}</TD>
                    </TR>
                </template>
            </Table>
        </div>
    </Modal>
</template>

<script>
import Modal from '../../ui/modals/modal';
import Table from '../../ui/tables/Table';
import TH from '../../ui/tables/TH';
import TR from '../../ui/tables/TR';
import TD from '../../ui/tables/TD';
export default {
    components: { Modal, Table, TH, TR, TD },
    props: {
        transactions: {
            type: Array,
            default: [],
        },
        title:{
            type: String,
            default: 'Транзакции'
        }
    },
    methods: {
        /**
         * Возвращает ссылку на товар.
         * @param {object} transaction
         * @returns {string}
         */
        getProductLink(transaction) {
            return '/#/products/' + transaction.product_id + '/edit';
        },

    }
}
</script>
