<template>
    <Card title="Поиск по товарам" style="min-height: 730px;">
        <div class="d-flex flex-row w-100">
            <SearchInput 
                v-model="filters.searchStringProduct" 
                @search="search()" 
                @blur="setLocalStorage()" 
                @clear="clearSearch()"
                :isLoading="states.isLoading" 
                :placeholder="placeholderProduct"
                :disabled="isProductDisabled" />
            <SearchInput 
                v-model="filters.searchStringOrder" 
                @search="search()" 
                @blur="setLocalStorage()" 
                @clear="clearSearch()"
                :isLoading="states.isLoading" 
                :placeholder="placeholderOrder"
                :disabled="isOrderDisabled" />
        </div>
        <div class="p-2 d-flex flex-row w-100">
            <IconCheckbox v-model="filters.hasRealStock" @click="setLocalStorage()" :icon="icons.realStock"
                title="Показывать товары, где есть реальный остаток" />
            <IconCheckbox v-model="filters.hasFreeStock" @click="setLocalStorage()" :icon="icons.freeStock"
                title="Показывать товары, где есть свободный остаток" />
            <IconCheckbox v-model="filters.maintainedBalanceState" @click="setLocalStorage()" :icon="icons.maintainedBalance"
                title="Показывать товары, где есть поддерживаемый остаток" />
            <IconCheckbox v-model="filters.hasExpected" @click="setLocalStorage()" :icon="icons.invoice"
                title="Показывать товары, которые ожидаются" />
            <IconCheckbox v-model="filters.hasReserve" @click="setLocalStorage()" :icon="icons.order"
                title="Показывать товары, которые зарезервированы" />

            <ClearButton @click="clearFilter()" :text="null" class="ms-4 border-0" title="Очистить фильтры" />
        </div>

        <MainTable v-if="isShowTable" :settings_prop="states" class="w-100 border-start-0 border-end-0 border-bottom-0">
            <template v-slot:thead>
                <TH>Артикул</TH>
                <TH>Название товара</TH>
                <TH align="center">
                    <font-awesome-icon :icon="icons.realStock" class="text-primary" />
                    Реальный остаток
                </TH>
                <TH align="center">
                    <font-awesome-icon :icon="icons.freeStock" class="text-primary" />
                    Свободный остаток
                </TH>
                <TH align="center">
                    <font-awesome-icon :icon="icons.maintainedBalance" class="text-primary" />
                    Поддерживаемый остаток
                </TH>
                <TH align="center">
                    <font-awesome-icon :icon="icons.invoice" class="text-primary" />
                    Ожидается
                </TH>
                <TH align="center">
                    <font-awesome-icon :icon="icons.order" class="text-primary" />
                    Резерв
                </TH>
                <TH width="124px">Заказы</TH>
                <TH align="center" width="115px">
                    <font-awesome-icon icon="fa-solid fa-tag" size="lg" class="text-primary pe-1" />
                    Распродажа
                </TH>
            </template>
            <template v-slot:tbody>
                <TR v-for="product in products" @click_row="$router.push('products/' + product.id + '/edit')"
                    :cursorPointer="true">
                    <TD>{{ product.main_sku }}</TD>
                    <TD>{{ product.name }}</TD>
                    <TD align="center">{{ product.real_stock }}</TD>
                    <TD align="center">{{ freeStock(product) }}</TD>
                    <TD align="center">{{ product.maintained_balance }}</TD>
                    <TD align="center">{{ product.expected }}</TD>
                    <TD align="center">{{ product.reserved }}</TD>
                    <TD>
                        <ProductOrdersPopup :content="product.orderPositions" />
                    </TD>
                    <TD>
                        <TableCheckbox :disabled="true" v-model="product.is_sale"></TableCheckbox>
                    </TD>
                </TR>
            </template>
        </MainTable>

        <div v-else class="d-flex justify-content-center align-items-center border-top w-100" style="height: 451px;">
            <font-awesome-icon :icon="icons.search" class="text-muted" style="font-size: 50px;" />
        </div>
    </Card>
</template>

<script>
import { productAPI } from '../../../api/products_api'
import moduleConfig from './config';
import faIcons from '../../../mixins/fa-icons';
import UIKit from '../../../ui/UIKit'

import ProductOrdersPopup from '../../../shared/popups/ProductOrdersPopup'
import SearchInput from './ui/SearchInput.vue';

import TableCheckbox from '../../../components/inputs/table_checkbox.vue'

export default {
    components: { SearchInput, ProductOrdersPopup, TableCheckbox },
    mixins: [faIcons, UIKit],
    data() {
        return {
            filters: {
                searchStringProduct: '',
                searchStringOrder: '',
            },
            products: [],
            states: {
                isLoading: false,
                isNoEntries: false,
                withThead: true,
            }
        }
    },
    methods: {
        async search() {
            this.filters.searchStringProduct = this.filters.searchStringProduct.trim();
            this.filters.searchStringOrder = this.filters.searchStringOrder.trim();
            if (
                this.filters.searchStringProduct.length >= moduleConfig.minSearchLength ||
                this.filters.searchStringOrder.length >= moduleConfig.minSearchLength
            ) {
                this.states.isLoading = true;
                await productAPI.search(this.getParams()).then((res) => {
                    this.products = res.data.data;
                    this.states.isLoading = false;

                    if (res.data.data.length) {
                        this.states.isNoEntries = false;
                    } else {
                        this.states.isNoEntries = true;
                    }
                }).catch(() => {
                    this.states.isLoading = false;
                })
            }
        },
        getParams() {
            let { hasRealStock, hasFreeStock, hasMaintainedBalance, hasReserve, hasExpected, searchStringProduct, searchStringOrder } = this.filters;
            const params = {
                searchStringProduct,
                searchStringOrder, hasRealStock,
                hasFreeStock, hasMaintainedBalance,
                hasReserve, hasExpected
            }

            return params;
        },
        setFiltersDefault() {
            this.filters = {
                searchStringProduct: this.filters.searchStringProduct,
                searchStringOrder: this.filters.searchStringOrder,
                hasRealStock: false,
                hasFreeStock: false,
                hasMaintainedBalance: false,
                hasReserve: false,
                hasExpected: false,
            }
        },
        setLocalStorage() {
            const localStorageFilters = JSON.stringify(this.filters);
            localStorage.setItem('homePageFilters', localStorageFilters);
        },
        clearFilter() {
            this.setFiltersDefault();
            this.setLocalStorage();
        },
        clearSearch() {
            this.products = [];
            this.states.isNoEntries = false;
            this.setLocalStorage();
        },
        freeStock(product) {
            let freeStock = product.real_stock - product.reserved;
            if (freeStock < 0)
                freeStock = 0;
            return freeStock;
        }
    },
    computed: {
        isProductDisabled() {
            return this.filters.searchStringOrder.length > 0;
        },
        isOrderDisabled() {
            return this.filters.searchStringProduct.length > 0;
        },
        placeholderProduct() {
            return this.isOrderDisabled ? 'Для ввода удалите текст из другого поля' : 'Введите название товара или артикул';
        },
        placeholderOrder() {
            return this.isProductDisabled ? 'Для ввода удалите текст из другого поля' : 'Введите номер заказа или CRM ID';
        },
        isShowTable() {
            return this.products.length || this.states.isLoading || this.states.isNoEntries
        }
    },
    mounted() {
        let localStorageFilters = localStorage.getItem('homePageFilters')
        if (localStorageFilters) {
            this.filters = JSON.parse(localStorageFilters);
        } else {
            this.setFiltersDefault();
            this.setLocalStorage();
        }
        this.search();
    }
}
</script>
