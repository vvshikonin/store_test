<template>
    <form @submit.prevent="">
        <DefaultModal width="900" title="Добавление товара на склад" @close_modal="$emit('close')">
            <div class="d-flex flex-column h-100 border-top">
                <div v-if="isLoading"
                    class="position-absolute w-100  text-primary d-flex justify-content-center align-items-center"
                    style="z-index: 2000; margin-top: -40px;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span class="ms-3">Обработка...</span>
                </div>
                <MainTable :table-settings="{ withFooter: false }" style="border: 0px!important; overflow-y: auto;">
                    <template v-slot:thead>
                        <TH>Артикул</TH>
                        <TH>Название</TH>
                        <TH>Поставщик</TH>
                        <TH>Количество</TH>
                        <TH>Цена</TH>
                        <TH align="center">Статус</TH>
                    </template>
                    <template v-slot:tbody>
                        <TR>
                            <TD width="250">
                                <input v-if="!correctionProduct.product_id" class="form-control"
                                    v-model="correctionProduct.sku" type="text" disabled
                                    style="font-size: 13px;">
                                <span v-else>{{ correctionProduct.sku }}</span>
                            </TD>
                            <TD width="600">
                                <span v-if="correctionProduct.product_id">{{ correctionProduct.name }}</span>
                                <input v-model="correctionProduct.name" v-else-if="correctionProduct.sku && !correctionProduct.product_id"
                                    type="text" class="form-control"
                                    placeholder="Товар не найден, введите название для создания нового товара."
                                    style="font-size: 13px;">
                                <span v-else class="text-muted">Введите артикул товара</span>
                            </TD>
                            <TD width="350">
                                <select placeholder="Выберите поставщика" v-model="correctionProduct.contractor_id" class="form-control w-50" style="height: 35px; font-size: 13px">
                                    <option v-for="contractor in contractors" :value="contractor.id">
                                        {{ contractor.name }}
                                    </option>
                                </select>
                            </TD>
                            <TD width="100">
                                <input class="form-control" @paste.prevent="pasteRevision_stock($event, product, index)"
                                    v-model="correctionProduct.revision_stock" disabled type="number" step="1" min="0"
                                    style="width: 70px; font-size: 13px;">
                            </TD>
                            <TD width="100">
                                <input class="form-control" @paste.prevent="pastePrice($event, product, index)"
                                    v-model="correctionProduct.price" type="number" step="0.01" min="1"
                                    style="width: 120px; font-size: 13px;">
                            </TD>
                            <TD width="200" align="center" class="bg-light">
                                <div class="ps-2">
                                    <span v-if="correctionProduct.product_id && correctionProduct.revision_stock && correctionProduct.price && correctionProduct.contractor_id"
                                        class="text-success">OK</span>
                                    <span v-else-if="correctionProduct.sku && !correctionProduct.product_id && !correctionProduct.name" class="text-danger">Товар
                                        на складе не найден. Укажите название товара</span>
                                    <button v-else-if="correctionProduct.sku && !correctionProduct.product_id" @click="createProduct(correctionProduct)"
                                        type="button" class="btn btn-info text-white" style="font-size: 13px;">
                                        Создать товар
                                        <font-awesome-icon icon="fa-solid fa-plus" class="ms-2" style="font-size: 13px;" />
                                    </button>
                                    <span v-else-if="!correctionProduct.contractor_id" class="text-warning">Не указан поставщик</span>
                                    <span v-else-if="!correctionProduct.revision_stock && correctionProduct.product_id" class="text-warning">Не указано
                                        количество</span>
                                    <span v-else-if="!correctionProduct.price && correctionProduct.product_id" class="text-warning">Не указана
                                        цена</span>
                                    <span v-else="correctionProduct.product_id" class="text-danger">Не указан артикул</span>
                                </div>
                            </TD>
                        </TR>
                    </template>
                </MainTable>
                <div class="mt-auto p-3 border-top bg-light">
                    <button type="button" @click="onAddProducts()" class="btn btn-primary me-1"
                        :disabled="!isProductsOk">Скорректировать</button>
                    <button type="button" @click="$emit('close')" class="btn btn-light border border-1">Отмена</button>
                </div>
            </div>

        </DefaultModal>
    </form>
</template>

<script>
import { productAPI } from '../../api/products_api.js'
import { mapGetters } from 'vuex';

import MainTable from '../Tables/main_table.vue';
import TH from '../Tables/th.vue';
import TR from '../Tables/tr.vue';
import TD from '../Tables/td.vue';
import SelectInput from '../inputs/filter_select_searchable.vue';
import AddButon from '../buttons/add_button.vue';
import DefaultModal from './default_modal.vue';

export default {
    components: { MainTable, TH, TR, TD, AddButon, SelectInput, DefaultModal },
    data() {
        return {
            isLoading: false,
            correctionProduct: {
                product_id: null,
                brand_id: null,
                sku: null,
                name: null,
                revision_stock: null,
                price: null,
                contractor_id: null,
                original_stock: 0,
                is_manually_added: 1
            },
        }
    },
    props: {
        product: Object,
    },
    computed: {
        ...mapGetters({contractors: 'getContractors'}),
        isProductsOk() {
            let status = true;

            if (!this.correctionProduct.product_id || !this.correctionProduct.revision_stock ||
                !this.correctionProduct.price || !this.correctionProduct.contractor_id) 
            {
                status = false
                return false;
            }

            return status;
        }
    },
    methods: {
        inputSku(product) {
            this.isLoading = true;
            productAPI.bulkSearch([product.manual_sku]).then(res => {
                if (res.data[product.manual_sku] !== null) {
                    this.correctionProduct.name = res.data[product.manual_sku].name;
                    this.correctionProduct.sku = res.data[product.manual_sku].main_sku;
                    this.correctionProduct.product_id = res.data[product.manual_sku].id;
                    this.correctionProduct.revision_stock = product.revision_stock;
                    this.correctionProduct.id = product.id;
                    this.correctionProduct.brand_id = product.brand_id;
                } else {
                    this.correctionProduct.id = product.id;
                    this.correctionProduct.brand_id = product.brand_id;
                    this.correctionProduct.name = product.manual_name;
                    this.correctionProduct.sku = product.manual_sku;
                    this.correctionProduct.revision_stock = product.revision_stock;
                }
                this.isLoading = false;
            });
        },

        async createProduct(product) {
            const reqProduct = { name: product.name, main_sku: product.sku, brand_id: product.brand_id };
            const res = await productAPI.store({ product: reqProduct });
            product.product_id = res.data.data.id;
        },

        onAddProducts() {
            this.$emit('add', this.correctionProduct);
            this.$emit('close');
        }
    },
    created() {
        this.$nextTick(() => {
            this.inputSku(this.product);
            this.$store.dispatch('loadContractorsData');
        })
    }
}
</script>