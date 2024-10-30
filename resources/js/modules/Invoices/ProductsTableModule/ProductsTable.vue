<template>
    <Card title="Состав счёта">
        <div class="p-3 d-flex w-100">
            <Transition>
                <div v-if="selected.length">
                    <IconButton v-if="canUserReceiveProduct" @click="receiveAllSelected()" class="btn-success"
                        text="Оприходовать" icon="fa-solid fa-check" />
                    <IconButton v-if="canUserRefuseProduct" @click="refuseAllSelected()" class="btn-danger"
                        text="Отказаться" icon="fa-solid fa-xmark" />
                </div>
            </Transition>
            <div class="ms-auto">
                <AddButton v-if="canUserAddProduct" @click="isShowAddProductsModal = true" text="Добавить товары" />
            </div>
        </div>

        <template v-if="invoice.products.length">
            <Table class="w-100 border-start-0 border-end-0 border-bottom-0">
                <template v-slot:thead>
                    <TH v-if="canUserReceiveProduct || canUserRefuseProduct" width="50" align="center">
                        <input v-model="isAllSelected" :indeterminate="isSelectedPartly" type="checkbox"
                            class="form-check-input table-input" />
                    </TH>
                    <TH width="150">Артикул</TH>
                    <TH width="300">Название</TH>
                    <TH width="100">Бренд</TH>
                    <TH width="100">Количество</TH>
                    <TH width="100">Цена</TH>
                    <TH width="100">Оприходовано</TH>
                    <TH width="100">Отказ</TH>
                    <TH width="100" align="center">Ожидается</TH>
                    <TH width="100">Дата доставки</TH>
                    <TH width="100" v-if="invoice.delivery_type == 2">Тип доставки</TH>
                    <TH width="100">Оприходован</TH>
                    <TH width="50" />
                </template>

                <template v-slot:tbody>
                    <TR v-for="product in invoice.products" :key="product.product_id">
                        <TD v-if="canUserReceiveProduct || canUserRefuseProduct" align="center">
                            <input v-model="selected" :value="product.product_id" type="checkbox"
                                class="ms-2 form-check-input" />
                        </TD>
                        <TD class="d-flex align-items-center">
                            <!-- <a v-if="!product.received && !isEditingProduct(product)" 
                                @click.prevent="toggleEdit(product)">
                                <font-awesome-icon class="ms-1 text-primary" icon="fa-solid fa-gear" />
                            </a>
                            <a v-else-if="isEditingProduct(product)" 
                                @click.prevent="handleSaveProduct(product)">
                                <font-awesome-icon class="ms-1 text-success" icon="fa-solid fa-check" />
                            </a> -->

                            <!-- Инлайн-редактирование только SKU -->
                            <template v-if="isEditingProduct(product)">
                                <input v-model="product.editSKU" @input="handleSKUChange(product)" 
                                    class="form-control" style="font-size: 13px" 
                                    placeholder="Введите артикул" required />
                            </template>
                            <template v-else>
                                <span><a :href="getProductLink(product)">{{ product.sku }}</a></span>
                            </template>
                        </TD>

                        <!-- Ячейка для названия товара -->
                        <TD>
                            <template v-if="isEditingProduct(product) && product.isNewProduct">
                                <input v-model="product.product_name_input" 
                                    @input="updateProductName(product.product_id, product.product_name_input)" 
                                    class="form-control" style="font-size: 13px"
                                    placeholder="Введите название товара" required />
                            </template>
                            <template v-else>
                                {{ product.product_name }}
                            </template>
                        </TD>
                        <TD>
                            <BrandSelect @change="setProductBrand(product, $event.target.value)"
                                :value="product.brand_id" :disabled="isBrandSelectDisabled(product)" :load="false"
                                class="table-input" required />
                        </TD>
                        <TD align="center">
                            <input @input="setAmount(product, $event.target.value)" :value="product.amount"
                                :disabled="!productPaymentValidation && product.id != 'new'" :min="product.refunded"
                                type="number" class="form-control table-input short" step="1" required>
                        </TD>
                        <TD>
                            <input @change="setPrice(product, $event.target.value)" :value="product.price"
                                :disabled="!productPaymentValidation && product.id != 'new'" type="number"
                                class="form-control table-input" step="0.01" min="0" required>
                        </TD>
                        <TD>
                            <input @input="setReceived(product, $event.target.value)" :value="product.received"
                                :disabled="!canUserReceiveProduct || !inDeliveryInterval(product)"
                                :min="product.refunded" :max="product.amount - product.refused" type="number"
                                class="form-control table-input short" step="1" required>
                        </TD>
                        <TD>
                            <input @input="setRefused(product, $event.target.value)" :value="product.refused"
                                type="number" :disabled="!canUserRefuseProduct" :max="product.amount - product.received"
                                class="form-control table-input short" step="1" min="0" required>
                        </TD>
                        <TD align="center" class="border-start border-end border-1 bg-light">
                            <span class="fw-bolder">{{ getExpected(product) }}</span>
                        </TD>
                        <TD>
                            <div class="d-flex justify-content-between align-items-center">
                                <input @input="setDeliveryDateFrom(product, $event.target.value)"
                                    :value="product.planned_delivery_date_from" :max="product.planned_delivery_date_to"
                                    type="date" class="form-control table-input">
                                <span>-</span>
                                <input @input="setDeliveryDateTo(product, $event.target.value)"
                                    :value="product.planned_delivery_date_to" :min="product.planned_delivery_date_from"
                                    type="date" class="form-control table-input">
                            </div>
                        </TD>
                        <TD v-if="invoice.delivery_type == 2">
                            <select v-model="product.delivery_type"
                                @change="setDeliveryType(product, $event.target.value)" class="form-select"
                                style="font-size: 13px;" required>
                                <option value="0">Курьером</option>
                                <option value="1">Самовывоз</option>
                            </select>
                        </TD>
                        <TD>
                            {{ formatDate(product.received_at, 'DD.MM.YYYY HH:mm:ss') }}
                        </TD>
                        <TD>
                            <TrashButton v-if="canUserDeleteProducts || product.id == 'new'"
                                @click="deleteProduct(product)" />
                        </TD>
                    </TR>
                </template>

                <template v-slot:tfoot>
                    <span class="pe-5">
                        Сумма по оприходу: {{ $store.getters['invoiceModule/getInvoiceSum'].priceFormat(true) }}
                    </span>
                    <span class="pe-5">
                        Сумма по отказу: {{ $store.getters['invoiceModule/getRefusedProductsSum'].priceFormat(true) }}
                    </span>
                    <span>
                        Сумма по счёту: {{ $store.getters['invoiceModule/getReceivedProductsSum'].priceFormat(true) }}
                    </span>
                </template>
            </Table>
        </template>

        <template v-else>
            <div class="d-flex flex-column align-items-center w-100 p-5 text-muted">
                <span>В счете нет товаров,</span>
                <span>добавьте их с помощью кнопки "Добавить товары" справа</span>
            </div>
        </template>

        <AddProductsModal v-if="isShowAddProductsModal" @close="isShowAddProductsModal = false" />
    </Card>
</template>


<style scoped>
.table-input {
    font-size: 13px
}

.table-input.short {
    width: 70px;
}

.table-input:not(.short):not(.form-check-input) {
    width: 135px
}
</style>

<script>
import InvoiceMixin from '../../../mixins/InvoiceMixin'

import Card from '../../../ui/containers/Card.vue'
import IconButton from './ui/IconButton.vue'
import AddButton from './ui/AddButton.vue'
import Table from '../../../ui/tables/Table.vue'
import TH from '../../../ui/tables/TH.vue'
import TR from '../../../ui/tables/TR.vue'
import TD from '../../../ui/tables/TD.vue'
import TrashButton from '../../../ui/buttons/TrashButton.vue';
import BrandSelect from '../../../shared/brands/Select.vue'
import AddProductsModal from './components/AddProductsModal.vue'

import moment from 'moment'

export default {
    components: { Card, IconButton, AddButton, Table, TH, TR, TD, TrashButton, BrandSelect, AddProductsModal },
    mixins: [InvoiceMixin],
    data() {
        return {
            selected: [],
            isShowAddProductsModal: false,
            isNewProduct: false,
            editingProductId: null,
            editedSku: '',
            editedProductName: '',
        }
    },
    computed: {
        /**
         * Определяет состояние атрибута `indeterminate` на чекбоксе выделения всех записей.
         */
        isSelectedPartly() {
            return !(this.invoice.products.length == this.selected.length) && this.selected.length;
        },
        isAllSelected: {
            get() {
                return this.invoice.products.length == this.selected.length;
            },
            set(value) {
                if (value) {
                    let productsIDs = [];
                    this.invoice.products.forEach(product => productsIDs.push(product.product_id));
                    this.selected = productsIDs;
                } else {
                    this.selected = [];
                }
            }
        },

    },
    mounted() {
        this.$store.dispatch('loadBrands')
    },
    methods: {
        /**
         * Определяет состояние атрибута `disabled` в таблице для `product`.
         * @param {object} product
         */
        isBrandSelectDisabled(product) {
            return (product.brand_id != null || !this.checkPermission('product_update'))
                // || (this.isEditingProduct(product) && !product.isNewProduct);
        },

        /**
         * Определяет находится ли товар в интервале даты даставки относительно текущего времени.
         * @param {object} product
         */
        inDeliveryInterval(product) {
            const startDate = product.planned_delivery_date_from;
            const endDate = product.planned_delivery_date_to;

            if ((startDate == null && endDate == null) ||
                (endDate == null && moment().isAfter(startDate)) ||
                (startDate == null && moment().isBefore(endDate))
            )
                return true;

            return moment().isBetween(startDate, endDate, 'days', '[]');
        },
        isEditingProduct(product) {
            return product.isEditing;
        },
        
        toggleEdit(product) {
            this.$store.commit('invoiceModule/setProductToEdit', {
                productID: product.product_id,
                newSKU: product.editSKU || product.sku
            });
        },
        
        async handleSKUChange(product) {
            if (!product.editSKU) return;

            // Вызов действия для обработки изменения SKU
            await this.$store.dispatch('invoiceModule/searchProductBySKU', {
                productID: product.product_id,
                newSKU: product.editSKU
            });
        },

        async handleSaveProduct(product) {
            await this.$store.dispatch('invoiceModule/confirmEditProduct', {
                productID: product.product_id
            });
        },
        updateProductName(productID, value) {
            this.setProductNameInput({ productID, value });
        },

        setProductNameInput(productID, value) {
            this.$store.commit('invoiceModule/setProductNameInput', { productID, value });
        },
        /**
         * Устанавливает все количество ожидаемого как оприходованное по выделенным товарам.
         */
        receiveAllSelected() {
            this.$store.dispatch('invoiceModule/receiveExpectedProductsByIDs', this.selected)
        },

        /**
         * Устанавливает все количество ожидаемого как отказ по выделенным товарам.
         */
        refuseAllSelected() {
            this.$store.dispatch('invoiceModule/refuseExpectedProductsByIDs', this.selected)
        },

        deleteProduct(product) {
            this.$store.commit('invoiceModule/deleteProduct', product.product_id);
        },

        getProductLink(product) {
            return '#/products/' + product.product_id + '/edit';
        },

        getExpected(product) {
            return this.$store.getters['invoiceModule/getProductExpected'](product.product_id);
        },

        setProductBrand(product, brandID) {
            this.$store.dispatch('invoiceModule/updateProductBrand', { productID: product.product_id, brandID })
        },

        setAmount(product, value) {
            this.$store.commit('invoiceModule/setProductAmountByID', { productID: product.product_id, value });
        },

        setPrice(product, value) {
            this.$store.commit('invoiceModule/setProductPriceByID', { productID: product.product_id, value });
        },

        setReceived(product, value) {
            this.$store.commit('invoiceModule/setProductReceivedByID', { productID: product.product_id, value });
        },

        setRefused(product, value) {
            this.$store.commit('invoiceModule/setProductRefusedByID', { productID: product.product_id, value });
        },

        setDeliveryDateFrom(product, value) {
            this.$store.commit('invoiceModule/setProductDeliveryDateFromByID', { productID: product.product_id, value });
        },

        setDeliveryType(product, value) {
            this.$store.commit('invoiceModule/setProductDeliveryTypeByID', { productID: product.product_id, value });
        },


        setDeliveryDateTo(product, value) {
            this.$store.commit('invoiceModule/setProductDeliveryDateToByID', { productID: product.product_id, value });
        },
    }
}
</script>
