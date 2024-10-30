<template>
    <form @submit.prevent="">
        <LargModal title="Массовое добавление товаров" @close_modal="$emit('close')">
            <div class="d-flex flex-column h-100 border-top">
                <div class="p-2 d-flex">
                    <div class="ps-2" style="font-size: 13px;">
                        <span class="text-primary pe-2">Всего в таблице: {{ products.length }}</span>
                        <span class="text-primary pe-2">Сумма по таблице: {{ tableSumm }}</span>
                    </div>
                    <AddButon @click="addProduct" class="ms-auto me-2" buttonTitle="Добавить"></AddButon>
                </div>
                <div v-if="isLoading"
                    class="position-absolute w-100  text-primary d-flex justify-content-center align-items-center"
                    style="z-index: 2000; margin-top: -40px;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span class="ms-3">Обработка...</span>
                </div>
                <MainTable :table-settings="{ withFooter: false }" style="border: 0px!important; overflow-y: auto;">
                    <template v-slot:thead>
                        <!-- <TH style="font-size: 13px!important;">
                            <input class="form-check-input " type="checkbox">
                        </TH> -->
                        <TH align="center">№</TH>
                        <TH>Артикул</TH>
                        <TH>Название</TH>
                        <TH>Бренд</TH>
                        <TH>Количество</TH>
                        <TH>Цена</TH>
                        <TH align="center">Статус</TH>
                        <TH align="center"></TH>
                    </template>
                    <template v-slot:tbody>
                        <TR v-for="product, index in products">
                            <!-- <TD align="center">
                                <input class="form-check-input ms-2" type="checkbox">
                            </TD> -->
                            <TD align="center">
                                {{ index + 1 }}
                            </TD>
                            <TD width="250">
                                <input v-if="!product.id" class="form-control" @change="inputSku(product)"
                                    @paste.prevent="pasteSku($event, product, index)" v-model="product.sku" type="text"
                                    style="font-size: 13px;">
                                <span v-else>{{ product.sku }}</span>
                            </TD>
                            <TD width="863.20">
                                <span v-if="product.id">{{ product.name }}</span>
                                <input v-else-if="product.sku && !product.id && checkProductUnique(product, index)"
                                    @paste.prevent="pasteName($event, product, index)" v-model="product.name" type="text"
                                    class="form-control"
                                    placeholder="Товар не найден, проверьте артикул или введите название для создания нового товара."
                                    style="font-size: 13px;">
                                <span v-else class="text-muted">Введите артикул товара</span>
                            </TD>
                            <TD width="300">
                                <BrandSelect @change="setProductBrand(product)" v-model="product.brand_id" font-size="13px">
                                </BrandSelect>
                            </TD>
                            <TD width="100">
                                <input class="form-control" @paste.prevent="pasteAmount($event, product, index)"
                                    v-model="product.amount" type="number" step="1" min="0"
                                    style="width: 70px; font-size: 13px;">
                            </TD>
                            <TD width="100">
                                <input class="form-control" @paste.prevent="pastePrice($event, product, index)"
                                    v-model="product.price" type="number" step="0.01" min="1"
                                    style="width: 120px; font-size: 13px;">
                            </TD>
                            <TD width="200" align="center" class="bg-light">
                                <div class="ps-2">
                                    <span v-if="product.id && product.amount && product.price && product.brand_id"
                                        class="text-success">OK</span>
                                    <span v-else-if="!checkProductUnique(product, index)" class="text-danger">Этот артикул
                                        уже
                                        есть в списке</span>
                                    <span v-else-if="product.sku && !product.id && !product.name" class="text-danger">Товар
                                        не
                                        найден.</span>
                                    <button v-else-if="product.sku && !product.id" @click="createProduct(product)"
                                        type="button" class="btn btn-info text-white" style="font-size: 13px;"
                                        :disabled="!product.brand_id">
                                        {{ !product.brand_id ? "Укажите бренд" : "Создать товар" }}
                                        <font-awesome-icon icon="fa-solid fa-plus" class="ms-2" style="font-size: 13px;" />
                                    </button>
                                    <span v-else-if="!product.amount && product.id" class="text-danger">Не указано
                                        количество</span>
                                    <span v-else-if="!product.price && product.id" class="text-danger">Не указана
                                        цена</span>
                                    <span v-else-if="!product.brand_id" class="text-danger">Не указан бренд</span>
                                    <span v-else="product.id" class="text-danger">Не указан артикул</span>
                                </div>

                            </TD>
                            <TD align="center" width="100">
                                <TrashButton @click="deleteProduct(product)"></TrashButton>
                            </TD>
                        </TR>
                    </template>
                </MainTable>
                <div class="mt-auto p-3 border-top bg-light">
                    <button type="button" @click="onAddProducts()" class="btn btn-primary me-1"
                        :disabled="!isProductsOk">Добавить</button>
                    <button type="button" @click="$emit('close')" class="btn btn-light border border-1">Отмена</button>
                </div>
            </div>

        </LargModal>
    </form>
</template>
<script>
import { productAPI } from '../../api/products_api.js'
import BrandSelect from '../../shared/selects/BrandSelect.vue';

import LargModal from './large_modal.vue';
import MainTable from '../Tables/main_table.vue';
import TH from '../Tables/th.vue';
import TR from '../Tables/tr.vue';
import TD from '../Tables/td.vue';
import TrashButton from '../inputs/trash_button.vue';
import AddButon from '../buttons/add_button.vue';


export default {
    components: { LargModal, MainTable, TH, TR, TD, TrashButton, AddButon, BrandSelect },
    data() {
        return {
            products: [{ sku: null, name: null }],
            isLoading: false,
        }
    },
    computed: {
        tableSumm() {
            let sum = this.products.reduce((sum, product) => sum + (isNaN(product.amount * product.price) ? 0 : product.amount * product.price), 0).priceFormat(true);
            return sum;
        },
        isProductsOk() {
            let status = true;
            if (!this.products.length) return false;
            this.products.forEach((product, index) => {
                if (!product.id || !product.amount || !product.price || !product.brand_id || !this.checkProductUnique(product, index)) {
                    status = false
                    return false;
                }

            }, status)
            return status;
        }
    },
    methods: {
        addProduct() {
            this.products.push({ sku: null, name: null })
        },
        inputSku(product) {
            if (!product.sku) return;
            this.isLoading = true;
            productAPI.bulkSearch([product.sku]).then(res => {
                Object.entries(res.data).forEach(res_data => {
                    if (res_data[1] !== null) {
                        const found = this.products.find(product => product.sku == res_data[0])
                        found.name = res_data[1].name;
                        found.sku = res_data[1].main_sku;
                        found.id = res_data[1].id;
                        found.brand_id = res_data[1].brand_id;
                    }
                })
                this.isLoading = false;
            });
        },
        pasteSku(event, product, index) {
            const clipboardContent = this.eventClipboardTextToArray(event);

            product.sku = clipboardContent[0].trim();
            clipboardContent.slice(1).forEach((sku, clIndex) => {
                index++;
                clipboardContent[clIndex] = clipboardContent[clIndex].trim()
                if (this.products.length == index) {
                    this.products[index] = { sku, name: null };
                } else {
                    this.products[index].sku = sku;
                }
            });
            this.isLoading = true;
            productAPI.bulkSearch(clipboardContent).then(res => {
                Object.entries(res.data).forEach(res_data => {
                    if (res_data[1] !== null) {
                        const found = this.products.find(product => product.sku == res_data[0])
                        found.name = res_data[1].name;
                        found.sku = res_data[1].main_sku;
                        found.id = res_data[1].id;
                        found.brand_id = res_data[1].brand_id;
                    }
                })
                this.isLoading = false;
            });
        },
        pasteAmount(event, product, index) {
            const clipboardContent = this.eventClipboardTextToArray(event);

            product.amount = clipboardContent[0];
            clipboardContent.slice(1).forEach(amount => {
                index++;
                if (this.products.length == index) {
                    this.products[index] = { amount, name: null };
                } else {
                    this.products[index].amount = amount;

                }
            });
        },
        pastePrice(event, product, index) {
            const clipboardContent = this.eventClipboardTextToArray(event);

            product.price = this.strToPriceFormat(clipboardContent[0]);
            clipboardContent.slice(1).forEach(price => {
                index++;
                if (this.products.length == index) {
                    this.products[index] = { price: this.strToPriceFormat(price), name: null };
                } else {
                    this.products[index].price = this.strToPriceFormat(price);
                }
            });
        },
        pasteName(event, product, index) {
            const clipboardContent = this.eventClipboardTextToArray(event);
            product.name = clipboardContent[0];
            clipboardContent.slice(1).forEach(name => {
                index++;
                if (this.products.length == index) {
                    this.products[index] = { name };
                } else {
                    this.products[index].name = this.products[index].id ? this.products[index].name : name;
                }
            });
        },
        deleteProduct(product) {
            this.removeItemFromArray(this.products, product);
        },
        eventClipboardTextToArray(event) {
            return event.clipboardData.getData('Text').trim().split(/\r\n|\r|\n/g);
        },
        strToPriceFormat(numString) {
            return parseFloat(numString.replace(/\s/g, '').replace(',', '.').trim()).toFixed(2);
        },
        async createProduct(product) {
            const reqProduct = { name: product.name, main_sku: product.sku, sku_list: [product.sku], brand_id: product.brand_id };
            const res = await productAPI.store({ product: reqProduct });
            product.id = res.data.data.id;
        },
        checkProductUnique(product, index) {
            if (product.sku !== null) {
                const arrIndex = this.products.findIndex(arrayProduct => arrayProduct.sku == product.sku);
                if (index === arrIndex) return true;
                return false;
            }
            return true;
        },

        onAddProducts() {
            this.$emit('add', this.products)
            this.$emit('close')
        },
        setProductBrand(product) {
            if (product.id) {
                const updatedProduct = {
                    id: product.id,
                    brand_id: product.brand_id
                }
                productAPI.update({ product: updatedProduct })
            }

        }
    },
}
</script>
