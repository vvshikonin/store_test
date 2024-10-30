<template>
    <large_modal title="Выбрать товар" @close_modal="onCloseWindow()">
        <div class="d-flex bg-white flex-row justify-content-between h-100">
            <form @submit.prevent="onSearchProduct()"
                class="bg-light border-end border-top rounded-start-bottom d-flex flex-column justify-content-start p-3 h-100 w-25"
                style="min-width: 200px;">
                <div>
                    <label class="text-muted" style="font-size: 13px;" for="">Товар</label>
                    <input v-model="params.sku_filter" type="text" style="height: 30px; font-size: 13px;"
                        placeholder="Артикул или название" class="form-control">
                    <Select class="product-modal-brand-select w-100" v-model="params.brand_id_filter" :options="brands"
                        placeholder="Выбрать бренд" label="Бренды"></Select>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn  btn-sm btn-primary me-2" :disabled="isCover || isLoading">
                        <font-awesome-icon icon="fa-solid fa-magnifying-glass" />
                        Поиск
                    </button>
                    <button v-if="isFilterApplied" @click="onCancelFilter()" type="button"
                        class="btn btn-sm btn-light border text-danger" :disabled="isCover || isLoading">
                        <font-awesome-icon icon="fa-solid fa-xmark" class="me-1" />Очистить
                    </button>
                </div>
            </form>

            <MainTable v-if="!isProductNotFound"
                class="border-start-0 border-end-0 border-top-0 border-bottom-0 w-75 h-100"
                style="min-width: 150px; border-radius: 0px!important; overflow-x: auto;"
                :style="{'overflow-y': isCover ? 'hidden': 'auto'}" v-bind:key="products" :tableSettings="{ isLoading, isCover }">
                <template v-slot:thead>
                    <TableHeader>Основной артикул</TableHeader>
                    <TableHeader>Все артикулы</TableHeader>
                    <TableHeader>Название товара</TableHeader>
                    <TableHeader>Бренд</TableHeader>
                </template>
                <template v-slot:tbody>
                    <TableRow v-for="product in products" @click_row="onSelectProduct(product)">
                        <TableCell class="ps-3" style="cursor: pointer!important;">{{ product.main_sku }}</TableCell>
                        <TableCell class="ps-3" style="cursor: pointer!important;">
                            <template v-for="sku, index in product.sku_list">
                                <span class="text-primary">{{ sku }}</span>
                                <span v-if="product.sku_list.length !== index + 1">,</span>
                            </template>
                        </TableCell>
                        <TableCell style="cursor: pointer!important;">{{ product.name }}</TableCell>
                        <TableCell style="cursor: pointer!important;">{{ product.brand }}</TableCell>
                    </TableRow>
                </template>
            </MainTable>
            <div v-else class="d-flex flex-column align-items-center justify-content-center w-75 h-100 m-auto border-top">
                <h3 class="text-muted">По вашему запросу товаров не найдено.</h3>
                <h6 class="text-muted">Попробуйте сбросить фильтры или создать новый товар, если такого нет.</h6>
                <a href="#/products/new" target="_blank" class="btn btn-light border border-secondary bg-opacity"
                    style="font-size: 14px;" type="button">
                    Добавить товар <font-awesome-icon class="ms-1 text-success" icon="fa-solid fa-plus" />
                </a>
            </div>
        </div>
    </large_modal>
</template>

<style>
.product-modal-brand-select .select-searchable p,
.product-modal-brand-select .select-searchable div,
.product-modal-brand-select .select-searchable input {
    width: 100% !important;
}
</style>

<script>
import { productAPI } from '../../api/products_api';
import { brandsAPI } from '../../api/brand_api';
import { mapGetters } from 'vuex';
import large_modal from './large_modal.vue';
import MainTable from '../Tables/main_table.vue';
import TableHeader from '../Tables/th.vue';
import TableRow from '../Tables/tr.vue';
import TableCell from '../Tables/td.vue';
import Select from '../inputs/filter_select_searchable.vue';

export default {
    components: { MainTable, TableHeader, TableRow, TableCell, Select, large_modal },
    data() {
        return {
            isProductNotFound: false,
            params: {
                sku_filter: null,
                brand_id_filter: null,
            },
            isCover: false,
            isLoading: true,
            isFilterApplied: false,
            brands: null,
        }
    },
    emits: ['close_modal', 'select_product'],
    computed: {
        ...mapGetters({ products: 'getProducts' })
    },
    methods: {
        onCloseWindow() {
            this.isShowProductSelect = false;
            this.clearFilter();
            this.$emit('close_modal');
        },
        onCancelFilter() {
            this.clearFilter();
            this.onSearchProduct();
            this.isProductNotFound = false;
            this.isFilterApplied = false;
        },
        clearFilter() {
            this.params.brand_id_filter = null;
            this.params.sku_filter = null;
        },
        onSearchProduct() {
            this.products = [];
            this.isCover = true;
            this.isFilterApplied = true;
            this.indexProducts();
        },
        onSelectProduct(product) {
            this.$emit('select_product', product);
        },
        async indexProducts() {
            const params = this.params;
            this.loadBrands();
            this.$store.dispatch('indexProducts', { params }).then(() => {
                this.isProductNotFound = this.products.length ? false : true;
                this.isCover = false;
                this.isLoading = false
            });
        },
        async loadBrands() {
            const response = await brandsAPI.index();
            this.brands = response.data.data;
        },
    },
    mounted() {
        this.indexProducts();
    }
}
</script>
