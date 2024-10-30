<template>
    <Modal title="Добавить товары">
        <AddProductsInfo />
        <div class="d-flex p-3 w-100">
            <div>
                <div v-if="newProducts.length" class="text-muted">
                    <small class="pe-3">Количество: {{ newProducts.length }}</small>
                    <small>Сумма: {{ productsSum.priceFormat(true) }}</small>
                </div>

                <div class="d-flex flex-row mt-3" style="font-size: 13px;">
                    <small>Бренд по-умолчанию</small>
                    <BrandSelect v-model="default_brand" @change="setDefaultBrand" :load="false" />
                </div>

            </div>


            <div class="ms-auto">
                <AddButton @click="pushNewProduct()" text="Добавить" />
            </div>
        </div>

        <form @submit.prevent="AddProducts()">
            <template v-if="newProducts.length">
                <div>
                    <SpinnerBlock v-if="isLoading"
                        class="position-fixed align-items-center bg-secondary w-100 h-100 top-0 start-0 bg-opacity-25"
                        style="z-index: 10000;" />
                </div>
                <div style="overflow-y: auto; max-height: 59vh;">
                    <Table class="w-100 border-start-0 border-end-0 border-bottom-0">
                        <template v-slot:thead>
                            <TH width="50"></TH>
                            <TH width="100">Артикул</TH>
                            <TH>Название</TH>
                            <TH width="100">Бренд</TH>
                            <TH width="100">Количество</TH>
                            <TH width="100">Цена</TH>
                            <TH width="50" />
                        </template>
                        <template v-slot:tbody>
                            <TR v-for="product, index in newProducts">
                                <TD align="center" width="50">
                                    <font-awesome-icon v-if="product.isDublicate" class="text-danger"
                                        icon="fa-solid fa-triangle-exclamation" size="lg"
                                        title="Этот товар уже есть в списке или в счёте." />
                                </TD>
                                <TD :title="product.sku">
                                    <input v-model="product.sku" @change="inputSKU(product)"
                                        @paste.prevent="pasteSku($event, index)" :disabled="product.product_id"
                                        type="text" class="form-control" style="font-size: 13px; max-width: 300px;"
                                        required>
                                </TD>
                                <TD width="500" :title="product.name">
                                    <input v-model="product.name" @paste.prevent="pasteName($event, index)" type="text"
                                        class="form-control" :disabled="product.product_id"
                                        style="font-size: 13px; max-width: 500px;" required>
                                </TD>
                                <TD>
                                    <BrandSelect v-model="product.brand_id" @change="setProductBrand(product)"
                                        :load="false" :disabled="isBrandSelectDisabled(product)"
                                        style="font-size: 13px; max-width: 200px;" required />
                                </TD>
                                <TD>
                                    <input v-model="product.amount" @paste.prevent="pasteAmount($event, index)"
                                        @change="inputAmount(product)" type="number" class="form-control" min="0"
                                        step="1" style="font-size: 13px; max-width: 150px;" required>
                                </TD>
                                <TD>
                                    <input v-model="product.price" @paste.prevent="pastePrice($event, index)"
                                        @change="inputPrice(product)" type="number" class="form-control" min="0"
                                        step="0.01" style="font-size: 13px; max-width: 150px;" required>
                                </TD>
                                <TD>
                                    <TrashButton @click="deleteProduct(index)" />
                                </TD>
                            </TR>
                        </template>
                    </Table>
                </div>
            </template>
            <template v-else>
                <div class="d-flex flex-column align-items-center w-100 p-5 text-muted border-bottom border-top">
                    <span>Нет товаров для добавления,</span>
                    <span>добавьте их с помощью кнопки "Добавить" справа</span>
                </div>
            </template>

            <div class="bg-light rounded-bottom p-3">
                <button type="submit" class="btn btn-primary me-2" :disabled="isAddButtonDisabled">Добавить</button>
                <button @click="$emit('close')" type="button" class="btn btn-light border border-1">Отмена</button>
            </div>
        </form>
    </Modal>
</template>
<script>
import _ from 'lodash';

import { productAPI } from '../../../../api/products_api';

import Modal from '../../../../ui/modals/modal.vue'
import AddProductsInfo from './AddProductsInfo.vue';
import AddButton from '../ui/AddButton.vue';
import Table from '../../../../ui/tables/Table.vue'
import TH from '../../../../ui/tables/TH.vue'
import TR from '../../../../ui/tables/TR.vue'
import TD from '../../../../ui/tables/TD.vue'
import TrashButton from '../../../../ui/buttons/TrashButton.vue';
import BrandSelect from '../../../../shared/brands/Select.vue'
import SpinnerBlock from '../../../../ui/spinners/SpinnerBlock'

export default {
    components: { Modal, AddProductsInfo, AddButton, Table, TH, TR, TD, TrashButton, BrandSelect, SpinnerBlock },
    data() {
        return {
            newProducts: [{}],
            isLoading: false,
            default_brand: null,
        }
    },
    computed: {
        /**
         * Обрабатывает состояние атрибута `disabled` на кнопке 'Добавить'.
         */
        isAddButtonDisabled() {
            let hasDublicate = false;
            this.newProducts.every(product => {
                if (product.isDublicate) {
                    hasDublicate = true;
                    return false;
                }
                return true;
            });

            return !this.newProducts.length || hasDublicate;
        },

        /**
         * Возвращает сумма товаров.
         */
        productsSum() {
            return this.newProducts.reduce((sum, product) => {
                const productSum = parseInt(product.amount) * parseFloat(product.price).toFixed(2);
                if (Number.isNaN(productSum))
                    return sum += 0;
                else return sum += productSum;
            }, 0);
        }
    },
    methods: {
        /**
         * Добавляет товары в счёт.
         */
        AddProducts() {
            this.isLoading = true;
            this.newProducts.forEach(async product => {
                if (!product.product_id) {
                    const newProduct = { name: product.name, main_sku: product.sku, sku_list: [product.sku], brand_id: product.brand_id };
                    const res = await productAPI.store({ product: newProduct });
                    product.product_id = res.data.data.id;
                }
                this.$store.commit('invoiceModule/addProduct', product)
            });
            this.isLoading = false;
            this.$emit('close');
        },

        pushNewProduct() {
            this.newProducts.push({ brand_id: this.default_brand })
        },

        setDefaultBrand() {
            this.newProducts.forEach(product => {
                if (!product.brand_id) {
                    product.brand_id = this.default_brand;
                }
            });
        },

        /**
         * Задаёт поля переданному `newProduct` из переданного `res`.
         * @param {object} newProduct
         * @param {AxiosResponse} response
         */
        setNewProductFromResponse(newProduct, response) {
            if (!newProduct.sku)
                return;

            const storeProduct = response.data[newProduct.sku.trim()];
            if (storeProduct !== null) {
                newProduct.sku = storeProduct.main_sku;
                newProduct.name = storeProduct.name;
                newProduct.brand_id = storeProduct.brand_id;
                newProduct.product_id = storeProduct.id;
            }
        },

        /**
         * Проверяет список товаров на дубликаты.
         */
        checkDublicates() {
            this.newProducts.forEach(newProduct => {
                newProduct.isDublicate = false;
                const isInvoiceProductDublicate = !!this.$store.getters['invoiceModule/getProduct'](newProduct.product_id);

                const isProductIDDublicate = this.newProducts.filter(nP => {
                    if (nP.product_id)
                        return nP.product_id == newProduct.product_id;
                    return false;
                }).length > 1;

                const isProductSKUDUblicate = this.newProducts.filter(nP => {
                    if (nP.sku)
                        return nP.sku.trim().toLowerCase() == newProduct.sku.trim().toLowerCase();
                    return false;
                }).length > 1;

                if (isInvoiceProductDublicate || isProductIDDublicate || isProductSKUDUblicate)
                    newProduct.isDublicate = true;
            });
        },

        /**
         * Удаляет товар по индексу.
         * @param {number} index
         */
        deleteProduct(index) {
            this.newProducts.splice(index, 1);
            this.checkDublicates();
        },

        /**
         * Вставляет в каждый элемент `newProducts` начиная с `index` в поле `field` значения переданные в `data`,
         * предварительно передавая значение в `callback`.
         * @param {string} field
         * @param {number} index
         * @param {any[]} data
         * @param {function} callback
         */
        setFromArray(field, index, data, callback = (value, element) => value) {
            data.forEach(item => {
                item = item.trim();
                if (this.newProducts.length == index) {
                    this.newProducts[index] = {};
                    this.newProducts[index][field] = callback(item, this.newProducts[index]);
                    this.newProducts[index].brand_id = this.default_brand;
                } else {
                    this.newProducts[index][field] = callback(item, this.newProducts[index]);
                    this.newProducts[index].brand_id = this.default_brand;
                }
                index++;
            });
        },

        /**
         * Преобразует строку из буфера обмена события в массив,
         * разделяя её по переносу на новую строку формата `Excel`.
         * @param {event} event
         */
        clipboardDataToArray(event) {
            return event.clipboardData.getData('Text').trim().split(/\r\n|\r|\n/g).map(item => item.replace(/\t+/g, ''));
        },

        /**
         * Обрабатывает изменение поля `sku` на форме.
         * @param {object} newProduct
         */
        async inputSKU(newProduct) {
            newProduct.sku = newProduct.sku.trim();

            this.isLoading = true;
            const res = await productAPI.bulkSearch([newProduct.sku.trim()]);
            this.setNewProductFromResponse(newProduct, res)
            this.isLoading = false;

            this.checkDublicates();
        },

        /**
         * Обрабатывает вставку в поле 'sku' на форме.
         * @param {event} event
         * @param {number} index
         */
        async pasteSku(event, index) {
            const clipboardContent = this.clipboardDataToArray(event);
            this.setFromArray('sku', index, clipboardContent);

            this.isLoading = true;
            const res = await productAPI.bulkSearch(clipboardContent);
            this.newProducts.filter(newProduct => clipboardContent.includes(newProduct.sku))
                .forEach(newProduct => this.setNewProductFromResponse(newProduct, res));
            this.isLoading = false;

            this.checkDublicates();
        },

        /**
         * Обрабатывает вставку в поле 'name' на форме.
         * @param {event} event
         * @param {number} index
         */
        pasteName(event, index) {
            const clipboardContent = this.clipboardDataToArray(event);
            this.setFromArray('name', index, clipboardContent, (value, element) => {
                if (!element.product_id)
                    return value;
                return element.name;
            });
        },

        /**
         * Обрабатывает изменение поля `amount` на форме.
         * @param {object} newProduct
         */
        inputAmount(newProduct) {
            newProduct.amount = parseInt(newProduct.amount);
        },

        /**
         * Обрабатывает вставку в поле 'amount' на форме.
         * @param {event} event
         * @param {number} index
         */
        pasteAmount(event, index) {
            const clipboardContent = this.clipboardDataToArray(event);
            this.setFromArray('amount', index, clipboardContent, value => parseInt(value));
        },

        /**
         * Обрабатывает изменение поля `price` на форме.
         * @param {object} newProduct
         */
        inputPrice(newProduct) {
            newProduct.price = parseFloat(newProduct.price).toFixed(2);
        },

        /**
         * Обрабатывает вставку в поле 'price' на форме.
         * @param {event} event
         * @param {number} index
         */
        pastePrice(event, index) {
            const clipboardContent = this.clipboardDataToArray(event);
            this.setFromArray('price', index, clipboardContent, value => parseFloat(value.replace(/\s+/g, '').replace(',', '.')).toFixed(2));
        },

        /**
         * Определяет состояние селектора бренда на форме, для переданного товара.
         * @param {object} newProduct
         */
        isBrandSelectDisabled(newProduct) {
            return newProduct.product_id && newProduct.brand_id
        },

        /**
         * Обновляет бренд для переданного товара.
         * @param {object} newProduct
         */
        async setProductBrand(newProduct) {
            if (newProduct.product_id) {
                const updatedProduct = {
                    id: newProduct.product_id,
                    brand_id: newProduct.brand_id
                }
                this.isLoading = true;
                await productAPI.update({ product: updatedProduct });
                this.isLoading = false;
            }
        }
    },
}
</script>
