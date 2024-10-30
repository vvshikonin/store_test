<template>
    <div class="w-100 p-3 d-flex flex-row justify-content-end">
        <div>
            <button :disabled="!canUserUpdateContractorRefund" @click="isShowAddModal = true" type="button" class="btn btn-sm btn-light border border-secondary">
                Добавить товары
                <font-awesome-icon class="text-success ps-1" icon="fa-solid fa-plus" />
            </button>
        </div>
    </div>
    <Table class="w-100 border-start-0 border-end-0 border-bottom-0 ">
        <template v-slot:thead>
            <TH></TH>
            <TH class="fw-bold">Артикул</TH>
            <TH>Товар</TH>
            <TH align="center" class="fw-bold">Количество</TH>
            <TH>Цена</TH>
            <TH>Сумма</TH>
            <TH></TH>
        </template>
        <template v-slot:tbody>
            <TR v-for="refundProduct in $store.state.contractorRefundModule.contractor_refund_products"
                :with-inner-table="true">
                <TD>
                    <a @click.stop :href="'#/products/' + refundProduct.invoice_product.product_id + '/edit'"
                        class="fw-bold" target="_blank">
                        {{ refundProduct.invoice_product.product.main_sku }}
                    </a>

                </TD>
                <TD>{{ refundProduct.invoice_product.product.name }}</TD>
                <TD align="center" class="bg-light border-start border-end border-1 fw-bold">{{ refundProduct.amount }}</TD>
                <TD>{{ refundProduct.invoice_product.price.priceFormat(true) }}</TD>
                <TD>{{ productSum(refundProduct).priceFormat(true) }}</TD>
                <TD align="end" class="col-1">
                    <div class="me-3">
                        <TrashButton :disabled="!canUserUpdateContractorRefund" @click="removeProduct(refundProduct)" />
                    </div>
                </TD>

                <template v-slot:sub-thead>
                    <TH>Поставщик</TH>
                    <TH align="center">Количество</TH>
                    <TH>Цена</TH>
                    <TH>Сумма</TH>
                </template>

                <template v-slot:sub-tbody>
            <TR v-for="stock in refundProduct.contractor_refund_stocks">

                <TD>{{ stock.stock.contractor.name }}</TD>
                <TD align="center">{{ stock.amount }}</TD>
                <TD>{{ stock.stock.price.priceFormat(true) }}</TD>
                <TD>{{ stockSum(stock).priceFormat(true) }}</TD>
            </TR>
        </template>
        </TR>
        </template>
        <template v-slot:tfoot>
            <span class="me-5">Сумма возврата по счёту: {{ sumByInvoice.priceFormat(true) }}</span>
            <span>Сумма возврата по остаткам: {{ sumByStocks.priceFormat(true) }}</span>
        </template>
    </Table>
    <AddRefundProductModal v-if="isShowAddModal" @close="isShowAddModal = false;" />
</template>

<script>
import ContractorRefundMixin from '../../../mixins/ContractorRefundMixin';

import Table from '../../../ui/tables/Table.vue';
import TH from '../../../ui/tables/TH.vue';
import TR from '../../../ui/tables/TR.vue';
import TD from '../../../ui/tables/TD.vue';
import TrashButton from '../../../ui/buttons/TrashButton.vue';
import AddRefundProductModal from './components/AddRefundProductModal.vue';
export default {
    components: { Table, TR, TH, TD, TrashButton, AddRefundProductModal },
    mixins: [ContractorRefundMixin],
    data() {
        return {
            isShowAddModal: false,
        }
    },
    computed: { 
        refundProducts() {
            return this.$store.state.contractorRefundModule.contractor_refund_products
        },
        sumByInvoice() {
            return this.refundProducts.reduce((sum, product) => sum + this.productSum(product), 0)
        },
        sumByStocks() {
            return this.refundProducts.reduce((sum, product) => {
                return sum + product.contractor_refund_stocks.reduce((stocksSum, stock) => stocksSum + this.stockSum(stock), 0)
            }, 0)
        }
    },
    methods: {
        removeProduct(refundProduct) {
            if (refundProduct !== 'new') {
                this.$store.state.contractorRefundModule.contractor_refund_products =
                    this.$store.state.contractorRefundModule.contractor_refund_products.filter(product =>
                        product.invoice_product_id !== refundProduct.invoice_product_id
                    );

                this.$store.state.contractorRefundModule.deleted_contractor_refund_products_ids.push(refundProduct.id)
            } else {
                this.$store.state.contractorRefundModule.contractor_refund_products =
                    this.$store.state.contractorRefundModule.contractor_refund_products.filter(product =>
                        product.invoice_product_id !== refundProduct.invoice_product_id
                    );
            }
        },
        stockSum(stock) {
            return stock.amount * stock.stock.price;
        },
        productSum(refundProduct) {
            return refundProduct.amount * refundProduct.invoice_product.price;
        }
    }
}
</script>