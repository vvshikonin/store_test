<template>
    <modal title="Добавить товар">
        <SpinnerBlock class="p-5" v-if="isLoading" />

        <template v-else>

            <div style="max-height: 60vh; overflow-y: auto;">
                <Table v-if="exceptExist.length" class="border-start-0 border-end-0 border-bottom-0">
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
                        <TR v-for="invoiceProduct in exceptExist" :withInnerTable="true" :key="invoiceProduct.id">
                            <template v-slot:default>
                                <TD class="fw-bold">{{ invoiceProduct.product.main_sku }}</TD>
                                <TD>{{ invoiceProduct.product.name }}</TD>
                                <TD align="center">{{ invoiceProduct.price.priceFormat(true) }}</TD>
                                <TD align="center">
                                    {{ availableProductAmount(invoiceProduct) }}

                                </TD>
                                <TD align="center">
                                    {{ inStock(invoiceProduct) }}
                                </TD>
                                <TD class="bg-light border-start border-1 fw-bold" align="center">
                                    {{ productRefunded(invoiceProduct) }}
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
                        <TR v-for="stock in invoiceProduct.stocks" :key="stock.id">
                            <TD class="col-3">
                                {{ stock.contractor.name }}
                            </TD>
                            <TD class="col-2" align="center">{{ stock.price.priceFormat(true) }}</TD>
                            <TD class="col-2" align="center">{{ stock.amount }}</TD>
                            <TD class="col-1">
                                <div class="d-flex justify-content-end">
                                    <input @input="changeRefundAmount($event, stock, invoiceProduct)"
                                        :max="maxStocksRefund(invoiceProduct, stock)" class="form-control bg-white ms-1"
                                        type="number" min="0" placeholder="" style="font-size: 13px; width: 80px;" />
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
            <button @click="addProducts()" type="button" class="btn btn-primary">
                Добавить
            </button>
        </div>
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
            isLoading: false,
        }
    },
    computed: {
        exceptExist() {
            let existing = [];
            this.$store.state.contractorRefundModule.contractor_refund_products.forEach(element => {
                existing.push(element.invoice_product_id);
            });
            return this.$store.state.contractorRefundModule.invoice.invoice_products.filter(element => !existing.includes(element.id));
        },
    },
    methods: {
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
            return available;
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
        inStock(product) {
            return product.stocks.reduce((sum, stock) => +sum + +(stock.amount), 0);
        },
        changeRefundAmount(event, stock, product) {
            let newValue = event.target.value || 0;
            let currentValue = stock.refund || 0;

            product.setRefunded = (product.setRefunded || 0) + newValue - currentValue;
            stock.refund = newValue;
        },
        productRefunded(product) {
            return product.stocks.reduce((sum, stock) => +sum + +stock.refund || 0, 0)
        },
        addProducts() {
            this.exceptExist.forEach(product => {
                console.log('product');
                if (product.setRefunded > 0) {
                    console.log('setRefunded');
                    const newProduct = {
                        id: 'new',
                        amount: this.productRefunded(product),
                        invoice_product_id: product.id,
                        invoice_product: product,
                    };

                    newProduct.contractor_refund_stocks = [];
                    product.stocks.forEach(stock => {
                        if (stock.refund > 0) {
                            const newStock = {
                                amount: stock.refund,
                                stock_id: stock.id,
                            }
                            newProduct.contractor_refund_stocks.push(newStock);
                        }
                    })

                    this.$store.state.contractorRefundModule.contractor_refund_products.push(newProduct);
                }
            });
            this.$emit('close');

        }
    },
}
</script>
