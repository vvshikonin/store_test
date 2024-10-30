<template>
    <modal title="Создать возврат поставщику">
        <form @submit.prevent="onCreateRefund()">
            <SpinnerBlock class="p-5" v-if="isLoading" />

            <template v-else>
                <div class="p-2">
                    <input v-model="searchString" class="form-control" type="text" placeholder="Поиск товара" />
                </div>

                <div class="p-2 text-muted">
                    <small class="pe-3">Сумма возврата по счёту: {{ refundSumByInvoice.priceFormat(true) }}</small>
                    <small>Сумма возврата фактическая: {{ refundSumInFact.priceFormat(true) }}</small>
                </div>

                <div style="max-height: 60vh; overflow-y: auto;">
                    <Table v-if="filteredProducts.length" class="border-start-0 border-end-0 border-bottom-0">
                        <template v-slot:thead>
                            <TH></TH>
                            <TH>Артикул</TH>
                            <TH>Название</TH>
                            <TH align="center">Цена по счёту</TH>
                            <TH align="center">Доступно под возврат</TH>
                            <TH align="center">Доступно на складе</TH>
                            <TH align="center">Возврат</TH>
                        </template>
                        
                        <template v-slot:tbody>
                            <TR v-for="product in filteredProducts" :withInnerTable="true" :key="product.id">
                                <template v-slot:default>
                                    <TD class="fw-bold">{{ product.sku }}</TD>
                                    <TD>{{ product.product_name }}</TD>
                                    <TD align="center">{{ product.price.priceFormat(true) }}</TD>
                                    <TD align="center">{{ availableProductAmount(product) }}</TD>
                                    <TD align="center">{{ product.in_stock }}</TD>
                                    <TD class="bg-light border-start border-1 fw-bold" align="center">
                                        {{ productRefundAmount(product) }}
                                    </TD>
                                </template>

                                <template v-slot:sub-thead>
                                    <TH>Поставщик</TH>
                                    <TH align="center">Цена на складе</TH>
                                    <TH align="center">В наличии</TH>
                                    <TH align="right">
                                        <span class="me-4">Возврат</span>
                                    </TH>
                                </template>

                                <template v-slot:sub-tbody>
                            <TR v-for="stock in product.stocks" :key="stock.id">
                                <TD class="col-3">{{ stock.contractor.name }}</TD>
                                <TD class="col-2" align="center">{{ stock.price.priceFormat(true) }}</TD>
                                <TD class="col-2" align="center">{{ stock.amount }}</TD>
                                <TD class="col-1">
                                    <div class="d-flex justify-content-end">
                                        <input v-model="stock.refund" :max="maxStocksRefund(product, stock)"
                                            class="form-control bg-white ms-1" type="number" min="0" placeholder=""
                                            style="font-size: 13px; width: 80px;" />
                                    </div>
                                </TD>
                            </TR>
                        </template>
                        </TR>
            </template>
            </Table>
            <div v-else class="p-5 text-center">
                <span>Нет товаров для возврата</span>
            </div>
            </div>
            </template>

            <div class="p-3 border-top">
                <CreateButton type="submit" :disabled="refundAmount == 0" />
            </div>
        </form>
    </modal>
</template>

<script>

import modal from '../../../../ui/modals/modal.vue';
import Table from '../../../../ui/tables/Table'
import CreateButton from '../../../../ui/buttons/CreateButton.vue'
import SpinnerBlock from '../../../../ui/spinners/SpinnerBlock.vue'
import TH from '../../../../ui/tables/TH'
import TR from '../../../../ui/tables/TR'
import TD from '../../../../ui/tables/TD'


export default {
    components: { modal, Table, TH, TR, TD, SpinnerBlock, CreateButton },
    data() {
        return {
            searchString: "",
            isLoading: true,
        }
    },
    computed: {
        /**
         * Возвращает `invoiceProducts` отфильтрованные на основе `searchString`.
         * @returns {object[]}
         */
        filteredProducts() {
            const searchString = this.searchString.trim().toLocaleLowerCase();
            return this.$store.state.invoiceModule.refundAvailableProducts.filter(product => {
                return product.sku.toLocaleLowerCase().includes(searchString) ||
                    product.product_name.toLocaleLowerCase().includes(searchString);
            });
        },

        /**
         * Возвращает сумма возврата по счёту.
         * @returns {number}
         */
        refundSumByInvoice() {
            return this.$store.state.invoiceModule.refundAvailableProducts.reduce((sum, product) => +sum + this.productRefundAmount(product) * product.price, 0);
        },

        /**
         * Возвращает фактическую сумма возврата.
         * @returns {number}
         */
        refundSumInFact() {
            return this.$store.state.invoiceModule.refundAvailableProducts.reduce((sum, product) => +sum + this.productStocksRefundSum(product), 0);
        },

        refundAmount (){
            return this.$store.state.invoiceModule.refundAvailableProducts.reduce((sum, product) => +sum + this.productRefundAmount(product), 0);
        }
    },
    methods: {

        /**
         * Отправляет запрос на создание возврата.
         */
        async onCreateRefund() {
            await this.$store.dispatch('invoiceModule/creatContractorRefund');
            this.showToast("OK", "Возврат поставщику создан! ", "success");
            this.$emit('close');
        },

        /**
         * Возвращает максимальное значение `stock.refund` на основе данных товара и остатка.
         * @param {object} product
         * @param {object} stock
         * @return {number}
         */
        maxStocksRefund(product, stock) {
            let available = this.availableProductAmount(product);
            let refund = this.productRefundAmount(product);
            let inStock = stock.amount;

            if (inStock < available)
                return inStock;
            return stock.refund + available - refund;
        },

        /**
         * Возвращает количество возврата товара на основе остатков.
         * @param {object} product
         * @returns {number}
         */
        productRefundAmount(product) {
            return product.stocks.reduce((sum, stock) => +sum + +stock.refund, 0);
        },

        /**
         * Возвращает сумму возврата товара на основе остатков.
         * @param {object} product
         * @returns {number}
         */
        productStocksRefundSum(product) {
            return product.stocks.reduce((sum, stock) => +sum + +(stock.refund * stock.price), 0);
        },

        /**
         * Возвращает количество товара доступного под возврат.
         * @param {object} product
         * @returns {number}
         */
        availableProductAmount(product) {
            return product.received - product.refunded;
        },

    },
    async mounted() {
        await this.$store.dispatch('invoiceModule/loadRefundAvailableProducts')
        this.isLoading = false;
    }
}
</script>
